<?php
namespace Selene\Components;

use Selene\Components\Attributes;
use Selene\Support\Str;

class Component {
    protected string $name;
    protected Attributes $attributes;
    protected array $props = [];
    protected array $slots = [];

    public function __construct(string $name, array $attributes = [], array $slots = []) {
        $this->name = $name;
        $this->slots = $slots;
        $this->setAttributes($attributes);
    }

    /**
     * Get the name of the component
     * 
     * @return string
     */
    public function getName() : string {
        return $this->name;
    }

    /**
     * Get the props for the component
     * 
     * @return array
     */
    public function getProps() : array {
        return $this->props;
    }

    /**
     * Set the props for the component
     * 
     * @param array $props
     * @return void
     */
    public function setProps(array $properties) : void {
        $newProperties = [];
        $definedProps = array_merge($properties, $this->slots);

        foreach ($definedProps as $key => $value) {
            if (is_numeric($key)) {
                $propName = $value;
                $defaultValue = null;
            } else {
                $propName = $key;
                $defaultValue = $value;
            }

            $attributeName = $this->getAttributeName($propName);

            if ($attributeName) {
                $newProperties[$propName] = $this->attributes->get($attributeName);
                $this->attributes->forget($attributeName);
            } else {
                $newProperties[$propName] = $defaultValue;
            }
        }

        $this->props = $newProperties;
    }

    /**
     * Get the attributes for the component
     * 
     * @return Attributes
     */
    public function getAttributes() : Attributes {
        return $this->attributes;
    }

    /**
     * Set the attributes for the component
     * 
     * @param array $attributes
     * @return void
     */
    public function setAttributes(array $attributes) : void {
        $newAttributes = [];

        foreach ($attributes as $key => $value) {
            if ($value instanceof Attributes) {
                $newAttributes = array_merge($newAttributes, $value->all());
            } else {
                $newAttributes[$key] = $value;
            }
        }

        $this->attributes = new Attributes($newAttributes);
    }

    private function getAttributeName(string $name) : ?string {
        foreach ([$name, Str::kebab($name)] as $attributeName) {
            if ($this->attributes->has($attributeName)) {
                return $attributeName;
            }
        }

        return null;
    }
}
