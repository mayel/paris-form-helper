<?php

class Helpers_Form
{
    /**
     * @var
     */
    private static $_instance = null;

    /**
     * @var
     */
    private $resource = null;
    /**
     * @var
     */
    private $action   = null;
    /**
     * @var
     */
    private $options  = array();


    /**
     * Constructor
     *
     * @param mixed     $resource
     * @param string    $action
     * @param array     $options
     */
    private function __construct($resource, $action, $options)
    {
        $this->resource = $resource;
        $this->action   = $action;
        $this->options  = $options;
    }

    /**
     *
     *
     * @param mixed     $resource
     * @param string    $action
     * @param array     $options
     *
     * @return
     */
    static function form_for($resource=null, $action=null, array $options=array())
    {
        if (! is_null(self::$_instance)) {
            self::$_instance = null;
            return ob_get_clean();
        }

        self::$_instance = new Helpers_Form($resource, $action, $options);

        ob_start();
        return self::$_instance;
    }

    /**
     *
     *
     * @return mixed
     */
    static function end_form_for()
    {
        if (is_null(self::$_instance)) {
            return;
        }

        echo self::$_instance->render_form(self::form_for());
    }

    /**
     * Render the form
     *
     * @param string $content   Content of the form
     *
     * @return string  Form in hmtl format
     */
    public function render_form($content)
    {
        $method = isset($this->options['method']) ? strtolower($this->options['method']) : 'get';

        if ($this->resource instanceof Model) {
            $method = $this->resource->is_new() ? 'post' : 'put';
        }

        if (! in_array($method, array('get', 'post'))) {
            $input_method = $this->tag('input', array('type' => 'hidden', 'name' => '_method','value' => strtoupper($method)));
            $content      = $input_method . $content;
        }

        $options = array_merge_recursive(array(
            'html' => array(
                'action' => $this->action,
                'method' => strtolower($method) == 'get' ? 'get' : 'post',
            ),
        ), $this->options);

        return $this->tag('form', $options['html'], $content);
    }

    /**
     *
     */
    public function label($name, $label=null, array $attributes=array(), array $options=array())
    {
        if (is_empty($label)) {
            $label = $name;
        }

        $attributes = array_merge(array(
            'for' => $this->id_attribute($name),
        ), $attributes);

        return $this->tag("label", $attributes, $label);
    }

    /**
     *
     */
    public function hidden_field($name, $value, array $attributes=array(), array $options=array())
    {
        $attributes = array_merge(array(
            'value' => $value,
        ), $attributes);

        return $this->input_field("hidden", $name, $attributes, $options);
    }

    /**
     *
     */
    public function text_field($name, array $attributes=array(), array $options=array())
    {
        return $this->input_field("text", $name, $attributes, $options);
    }

    /**
     *
     */
    public function email_field($name, array $attributes=array(), array $options=array())
    {
        return $this->input_field("email", $name, $attributes, $options);
    }

    /**
     *
     */
    public function file_field($name, array $attributes=array(), array $options=array())
    {
        $this->options['html']['enctype'] = 'multipart/form-data';

        return $this->input_field("file", $name, $attributes, $options);
    }

    /**
     *
     */
    public function password_field($name, array $attributes=array(), array $options=array())
    {
        return $this->input_field("password", $name, $attributes, $options);
    }

    /**
     *
     */
    public function date_field($name, array $attributes=array(), array $options=array())
    {
        $options = array_merge(array(
            'in_format' => 'Y-m-d',
            'format'    => 'Y-m-d',
        ), $options);

        return $this->datetime_field($name, $attributes, $options);
    }

    /**
     *
     */
    public function datetime_field($name, array $attributes=array(), array $options=array())
    {
        $options = array_merge(array(
            'in_format' => 'Y-m-d H:i:s',
            'format'    => 'Y-m-d H:i:s',
        ), $options);

        $value = $this->value_for_name($name, $attributes, $options, false);
        if ($date = $this->create_date_from_format($options['in_format'], $value)) {
            $value = $date->format($options['format']);
        }

        $attributes = array_merge(array(
            'value' => $value,
        ), $attributes);

        return $this->input_field("text", $name, $attributes, $options);
    }

    /**
     *
     */
    public function checkbox($name, array $attributes=array(), array $options=array())
    {
        $options = array_merge(array(
            'uncheck_value' => isset($options['uncheck_value']) ? $options['uncheck_value'] : '0',
            'check_value'   => isset($options['check_value']) ? $options['check_value'] : '1',
        ), $options);

        $checkbox_attributes = array_merge(array(
            'value' => $options['check_value'],
        ), $attributes);

        if ($this->resource->get($name) == $options['check_value']) {
            $checkbox_attributes['checked'] = 'checked';
        }

        return $this->hidden_field($name, $options['uncheck_value']) . $this->input_field('checkbox', $name, $checkbox_attributes);
    }

    /**
     *
     */
    private function input_field($type, $name, array $attributes=array(), array $options=array())
    {
        $attributes = array_merge(array(
            'type'  => $type,
            'id'    => $type === 'hidden' ? null : $this->id_attribute($name),
            'name'  => $this->name_attribute($name),
            'value' => $this->value_for_name($name, $attributes, $options, $type === 'password'),
        ), $attributes);

        return $this->tag('input', $attributes);
    }

    /**
     *
     */
    public function text_area($name, array $attributes=array(), array $options=array())
    {
        $attributes = array_merge(array(
            'id'    => $this->id_attribute($name),
            'name'  => $this->name_attribute($name),
        ), $attributes);

        return $this->tag('textarea', $attributes, $this->value_for_name($name, $attributes, $options));
    }

    /**
     *
     */
    public function collection_select($name, $collection, $value_property=null, $text_property=null, array $attributes=array(), array $options=array())
    {
        if (! is_array($collection) && ! $collection instanceof Iterator) {
            throw new Exception(printf('Argument 2 passed to %s must implement interface Iterator or array, %s given  (in <b>%s</b> line %d)',
                __METHOD__,
                gettype($collection),
                __FILE__,
                __LINE__
            ));
        }

        $options_tags = '';
        if (isset($options['prompt']) && ! empty($options['prompt'])) {
            $options_tags .= $this->tag('option', array(
                'value' => '',
            ), $options['prompt']);
        }
        elseif (isset($options['include_blank']) && $options['include_blank'] === true) {
            $options_tags .= $this->tag('option', array(
                'value' => '',
            ), '');
        }

        foreach ($collection as $k=>$entry)
        {
            $entry_value   = !is_null($value_property) ? $this->get_collection_option_property($entry, $value_property) : $k;
            $options_tags .= $this->tag('option', array(
                'value'    => $entry_value,
                'selected' => $entry_value == $this->value_for_name($name, $attributes, $options) ? 'selected' : null,
            ),
                !is_null($value_property) ? $this->get_collection_option_property($entry, $text_property) : $entry
            );
        }

        $attributes = array_merge(array(
            'id'    => $this->id_attribute($name),
            'name'  => $this->name_attribute($name),
        ), $attributes);

        return $this->tag('select', $attributes, $options_tags);
    }

    /**
     *
     */
    public function submit($label, array $attributes=array())
    {
        return $this->tag(
            'button',
            array_merge(array(
                'type' => 'submit',
            ), $attributes),
            $label
        );
    }

    /**
     *
     */
    private function tag($tag_name, $attributes, $content=null)
    {
        $attributes = $this->attributes_to_string($attributes);

        if (! is_null($content)) {
            return "<$tag_name$attributes>$content</$tag_name>\n";
        }

        return "<$tag_name$attributes />\n";
    }

    /**
     *
     */
    private function attributes_to_string(array $attributes=array())
    {
        $string_attributes = "";

        foreach (array_filter($attributes, create_function('$x', 'return isset($x);')) as $attribute=>$value) {
            $string_attributes .= " $attribute=\"$value\"";
        }

        return $string_attributes;
    }

    /**
     *
     */
    private function resource_name()
    {
        if ($this->resource instanceof Model) {
            return $this->to_camelcase(get_class($this->resource));
        }
        if ($this->resource instanceof StdClass) {
            return $this->to_camelcase(get_class($this->resource));
        }
        if (is_string($this->resource)) {
            return $this->resource;
        }

        return null;
    }

    /**
     *
     */
    private function id_attribute($id)
    {
        if ($this->resource_name()) {
            $id = $this->resource_name() . '_' . $id;
        }

        return strtolower($id);
    }

    /**
     *
     */
    private function name_attribute($name)
    {
        if ($this->resource_name()) {
            $name = $this->resource_name() . '[' . $name . ']';
        }

        return strtolower($name);
    }

    /**
     *
     */
    private function to_camelcase($str)
    {
        $str[0] = strtolower($str[0]);

        return preg_replace_callback(
            '/([A-Z])/',
            create_function('$c', 'return "_" . strtolower($c[1]);'),
            $str
        );
    }

    /**
     *
     */
    private function h($str, $quote_style = ENT_QUOTES, $charset='utf-8')
    {
        return htmlspecialchars($str, $quote_style, $charset);
    }

    /**
     *
     */
    private function value_for_name($name, array $attributes=array(), array $options=array(), $escape=true)
    {
        if (isset($options['always_empty']) && $options['always_empty'] === true) {
            return '';
        }

        $value = null;
        if (isset($attributes['value']) && $attributes['value'] === true) {
            $value = $attributes['value'];
        }
        elseif ($this->resource instanceof Model) {
            $value = $this->resource->get($name);
        }
        elseif ($this->resource instanceof StdClass) {
            $value = $this->resource->{$name};
        }
        elseif (is_array($this->resource)) {
            $value = isset($this->resource[$name]) ? $this->resource[$name] : null;
        }

        return $escape === true ? $this->h($value) : $value;
    }

    /**
     *
     */
    private function get_collection_option_property($entry, $property)
    {
        if ($entry instanceof Model) {
            return $entry->get($property);
        }
        if ($entry instanceof StdClass) {
            return $entry->{$property};
        }
        if (is_array($entry) && isset($entry[$property])) {
            return $entry[$property];
        }

        return null;
    }

    /**
     * Create DateTime from format
     */
    private function create_date_from_format($format, $value)
    {
        $ugly = strptime($value, strtr($format, array(
            'Y' => '%Y',
            'm' => '%m',
            'd' => '%d',
            'H' => '%I',
            'i' => '%M',
            's' => '%S',
        )));

        if ($ugly == false || ! empty($ugly['unparsed'])) {
            return false;
        }

        $date = new DateTime();
        $date->setDate($ugly['tm_year'] + 1900, $ugly['tm_mon'] + 1, $ugly['tm_mday']);
        $date->setTime($ugly['tm_hour'], $ugly['tm_min'], $ugly['tm_sec']);
        return $date;
    }
}
