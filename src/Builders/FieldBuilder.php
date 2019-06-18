<?php

namespace Grafite\FormMaker\Builders;

use DateTime;

class FieldBuilder
{
    /**
     * Create a submit button element.
     *
     * @param  string $value
     * @param  array  $options
     *
     * @return \Illuminate\Support\HtmlString
     */
    public function submit($value = null, $options = [])
    {
        return $this->makeInput('submit', null, $value, $options);
    }

    /**
     * Make an html button
     *
     * @param string $value
     * @param array $options
     *
     * @return \Illuminate\Support\HtmlString
     */
    public function button($value = null, $options = [])
    {
        if (! array_key_exists('type', $options)) {
            $options['type'] = 'button';
        }

        return $this->toHtmlString('<button' . $this->attributes($options) . '>' . $value . '</button>');
    }

    /**
     * Make an input string
     *
     * @param string $type
     * @param string $name
     * @param mixed $value
     * @param array $options
     *
     * @return string
     */
    public function makeInput($type, $name, $value, $options = [])
    {
        if ($value instanceof DateTime) {
            $value = $value->format($options['format'] ?? 'Y-m-d');
        }

        return '<input '.$this->attributes($options).' name="'.$name.'" type="'.$type.'" value="'.$value.'">';
    }

    /**
     * Build an HTML attribute string from an array.
     *
     * @param array $attributes
     *
     * @return string
     */
    public function attributes($attributes)
    {
        $html = [];

        foreach ((array) $attributes as $key => $value) {
            $element = $this->attributeElement($key, $value);

            if (! is_null($element)) {
                $html[] = $element;
            }
        }

        return count($html) > 0 ? ' ' . implode(' ', $html) : '';
    }

    /**
     * Build a single attribute element.
     *
     * @param string $key
     * @param string $value
     *
     * @return string
     */
    public function attributeElement($key, $value)
    {
        if (is_numeric($key)) {
            return $value;
        }

        if (is_bool($value) && $key !== 'value') {
            return $value ? $key : '';
        }

        if (is_array($value) && $key === 'class') {
            return 'class="' . implode(' ', $value) . '"';
        }

        if (! is_null($value)) {
            return $key . '="' . e($value, false) . '"';
        }
    }

    /**
     * Make text input.
     *
     * @param array  $config
     * @param string $population
     * @param mixed $custom
     *
     * @return string
     */
    public function makeCustomFile($name, $value, $options)
    {
        if (isset($options['multiple'])) {
            $name = $name.'[]';
        }

        unset($options['class']);

        $label = '<label class="custom-file-label" for="'.$options['id'].'">Choose file</label>';

        return '<div class="custom-file"><input '.$this->attributes($options['attributes']).' class="custom-file-input" type="file" name="'.$name.'">'.$label.'</div>';
    }

    /**
     * Make a textarea.
     *
     * @param string  $name
     * @param mixed $value
     * @param array $options
     *
     * @return string
     */
    public function makeTextarea($name, $value, $options)
    {
        return '<textarea '.$this->attributes($options['attributes']).' name="'.$name.'">'.$value.'</textarea>';
    }

    /**
     * Make a inline checkbox.
     *
     * @param string  $name
     * @param mixed $value
     * @param array $options
     *
     * @return string
     */
    public function makeCheckboxInline($name, $value, $options)
    {
        return $this->makeCheckbox($name, $value, $options);
    }

    /**
     * Make a inline radio.
     *
     * @param string $name
     * @param mixed $value
     * @param array $options
     *
     * @return string
     */
    public function makeRadioInline($name, $value, $options)
    {
        return $this->makeRadio($name, $value, $options);
    }

    /**
     * Make a select.
     *
     * @param string $name
     * @param mixed $value
     * @param array $options
     *
     * @return string
     */
    public function makeSelect($name, $selected, $options)
    {
        $selectOptions = '';

        if (isset($options['attributes']['multiple'])) {
            $name = $name.'[]';
        }

        foreach ($options['options'] as $key => $value) {
            $selectedValue = '';

            if (isset($options['attributes']['multiple']) && (is_object($selected) || is_array($selected))) {
                if (in_array($value, collect($selected)->toArray())) {
                    $selectedValue = 'selected';
                }
            }

            if ($selected === $value) {
                $selectedValue = 'selected';
            }

            $selectOptions .= '<option value="'.$value.'" '.$selectedValue.'>'.$key.'</option>';
        }

        return '<select '.$this->attributes($options['attributes']).' name="'.$name.'">'.$selectOptions.'</select>';
    }

    public function makeCheckInput($name, $value, $options)
    {
        dd('ok, this gets special wrappers');
    }

    /**
     * Make a checkbox.
     *
     * @param string $name
     * @param mixed $value
     * @param array $options
     *
     * @return string
     */
    public function makeCheckbox($name, $value, $options)
    {
        return '<input '.$this->attributes($options['attributes']).' type="checkbox" name="'.$name.'">';
    }

    /**
     * Make a radio.
     *
     * @param string $name
     * @param mixed $value
     * @param array $options
     *
     * @return string
     */
    public function makeRadio($name, $value, $options)
    {
        return '<input '.$this->attributes($options['attributes']).' type="radio" name="'.$name.'">';
    }

    /**
     * Make a relationship input.
     *
     * @param string $name
     * @param mixed $value
     * @param array $options
     *
     * @return string
     */
    public function makeRelationship($name, $value, $options)
    {
        $method = 'all';
        $class = $options['model'];

        if (!is_object($class)) {
            $class = app()->make($options['model']);
        }

        if (isset($options['method'])) {
            $method = $options['method'];
        }

        $items = $class->$method();

        if (isset($options['params'])) {
            $items = $class->$method($options['params']);
        }

        foreach ($items as $item) {
            $optionLabel = $options['model_options']['label'];
            $optionValue = $options['model_options']['value'];

            $options['options'][$item->$optionLabel] = $item->$optionValue;
        }

        return $this->makeSelect($name, $value, $options);
    }

    /**
     * Transform the string to an Html serializable object
     *
     * @param $html
     *
     * @return \Illuminate\Support\HtmlString
     */
    protected function toHtmlString($html)
    {
        return new HtmlString($html);
    }
}
