<?php

class Helpers_Form
{
    private static $_instance = null;

    private $resource = null;
    private $action   = null;
    private $options  = array();

    private function __construct($resource, $action, $options) {
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

    static function end_form_for() {
        if (is_null(self::$_instance)) {
            return;
        }

        echo self::$_instance->render_form(self::form_for());
    }

    public function render_form($content) {
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

    public function label($name, $label=null, array $attributes=array(), array $options=array()) {
        $attributes = array_merge(array(
            'for' => $this->id_attribute($name),
        ), $attributes);
        return $this->tag("label", $attributes, $label);
    }

    public function hidden_field($name, $value, array $attributes=array(), array $options=array())
    {
        $attributes = array_merge(array(
            'value' => $value,
        ), $attributes);
        return $this->input_field("hidden", $name, $attributes, $options);
    }

    public function text_field($name, array $attributes=array(), array $options=array()) {
        return $this->input_field("text", $name, $attributes, $options);
    }

    public function email_field($name, array $attributes=array(), array $options=array()) {
        return $this->input_field("email", $name, $attributes, $options);
    }

    public function password_field($name, array $attributes=array(), array $options=array()) {
        return $this->input_field("password", $name, $attributes, $options);
    }

    public function date_field($name, array $attributes=array(), array $options=array())
    {
        $options = array_merge(array(
            'format' => 'Y-m-d',
        ), $options);
        return $this->datetime_field($name, $attributes, $options);
    }

    public function datetime_field($name, array $attributes=array(), array $options=array())
    {
        $options = array_merge(array(
            'format' => 'Y-m-d H:i:s',
        ), $options);

        $value = $this->value_for_name($name, $attributes, $options, false);
        if (! empty($value)) {
            $date  = new DateTime($value);
            $value = $date->format($options['format']);
        }

        $attributes = array_merge(array(
            'value' => $value,
        ), $attributes);
        return $this->input_field("text", $name, $attributes, $options);
    }

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

    public function text_area($name, array $attributes=array(), array $options=array()) {
        $attributes = array_merge(array(
            'id'    => $this->id_attribute($name),
            'name'  => $this->name_attribute($name),
        ), $attributes);
        return $this->tag('textarea', $attributes, $this->value_for_name($name, $attributes, $options));
    }

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

    public function submit($label, array $attributes=array()) {
        return $this->tag(
            'button',
            array_merge(array(
                'type' => 'submit',
            ), $attributes),
            $label
        );
    }

    private function tag($tag_name, $attributes, $content=null)
    {
        $attributes = $this->attributes_to_string($attributes);
        if (! is_null($content)) {
            return "<$tag_name$attributes>$content</$tag_name>\n";
        }
        else {
            return "<$tag_name$attributes />\n";
        }
    }

    private function attributes_to_string(array $attributes=array()) {
        $string_attributes = "";
        foreach (array_filter($attributes, create_function('$x', 'return isset($x);')) as $attribute=>$value) {
            $string_attributes .= " $attribute=\"$value\"";
        }
        return $string_attributes;
    }

    private function id_attribute($id) {
        if ($this->resource) {
            $id = $this->resource_name() . '_' . $id;
        }
        return strtolower($id);
    }

    private function name_attribute($name) {
        if ($this->resource) {
            $name = $this->resource_name() . '[' . $name . ']';
        }
        return strtolower($name);
    }

    private function to_camelcase($str) {
        $str[0] = strtolower($str[0]);
        return preg_replace_callback(
            '/([A-Z])/',
            create_function('$c', 'return "_" . strtolower($c[1]);'),
            $str
        );
    }

    private function h($str, $quote_style = ENT_QUOTES, $charset='utf-8') {
        return htmlspecialchars($str, $quote_style, $charset); 
    }

    private function resource_name()
    {
        if ($this->resource instanceof Model) {
            return $this->to_camelcase(get_class($this->resource));
        }
        if (is_string($this->resource)) {
            return $this->resource;
        }
        return null;
    }

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

        return $escape === true ? $this->h($value) : $value;
    }
}
