<?php
namespace Selene;

class View {
    /**
     * Stored sections
     * 
     * @var array
     */
    private array $sections = [];

    /**
     * Parent sections
     * 
     * @var array
     */
    private array $parentSections = [];

    /**
     * Stack of section names
     * 
     * @var array
     */
    private array $sectionStack = [];

    /**
     * Define a section.
     * 
     * If content is provided, it's stored directly.
     * If content is null, starts output buffering and pushes the section name
     * to the stack for later capture with endSection().
     * If the section exists, it's saved as a parent section.
     * 
     * @param string $section The name of the section
     * @param string|null $content The content to store, or null to start buffering
     */
    public function section(string $section, ?string $content = null) : void {

        if (! $this->hasSection($section)) {
            $this->initSection($section);
        } else {
            $this->saveParentSection($section);
        }

        if ($content !== null) {
            $this->extendSection($section, $content);
        } else {
            $this->sectionStack[] = $section;
            ob_start();
        }
    }

    /**
     * End the current section and store its buffered content.
     * 
     * Captures the output buffer content and stores it in the section
     * that was started with section().
     * 
     * @throws \InvalidArgumentException if the section is not opened
     */
    public function endSection() : void {
        if (empty($this->sectionStack)) {
            throw new \InvalidArgumentException('Cannot end a section without opening one');
        }

        $section = array_pop($this->sectionStack);
        $this->extendSection($section, ob_get_clean());
    }

    /**
     * Check if a section is defined.
     * 
     * @param string $section The name of the section to check
     * @return bool True if the section is defined, false otherwise
     */
    public function hasSection(string $section) : bool {
        return isset($this->sections[$section]);
    }
    
    /**
     * Store a placeholder for the parent section.
     * 
     * The placeholder is used to indicate that the section content should be replaced
     * with the content of the parent section.
     * 
     * @see collapseSectionContent()
     * @return void
     */
    public function parentContent() : void {
        $section = end($this->sectionStack);
        $content = trim(ob_get_clean());

        if ($content) {
            $this->extendSection($section, $content);
        }

        // null is used as a placeholder for the parent content
        $this->extendSection($section, null);
        ob_start();
    }

    /**
     * Get the content of a section by name.
     * 
     * @param string $section The name of the section to retrieve
     * @param string $default The default value to return if the section is not defined
     * @return string The section content or empty string if not found
     */
    public function yield(string $section, string $default = '') : string {
        if (empty($this->parentSections[$section])) {
            return implode('', $this->sections[$section] ?? [$default]);
        }

        $sectionContentStack = $this->getSectionContentStack($section);

        return $this->collapseSectionContent($sectionContentStack);
    }

    /**
     * Recursively collapse sections by replacing null placeholders with parent content.
     * 
     * @param array $sections Array of section content arrays
     * @param int $index Current index in the sections array
     * @return string The collapsed section content
     */
    private function collapseSectionContent(array $sections, int $index = 0) : string {
        $currentSection = $sections[$index];
        $content = '';

        foreach ($currentSection as $value) {
            // replace null with the content of the parent section
            if (is_null($value)) {
                $content .= $this->collapseSectionContent($sections, $index + 1);
            } else {
                $content .= $value;
            }
        }

        return $content;
    }

    /**
     * Initialize a new section with empty arrays.
     * 
     * @param string $section The name of the section to initialize
     */
    private function initSection(string $section) : void {
        $this->sections[$section] = [];
        $this->parentSections[$section] = [];
    }

    /**
     * Add content to a section.
     * 
     * @param string $section The name of the section
     * @param string|null $content The content to add (null for parent placeholder)
     */
    private function extendSection(string $section, ?string $content) : void {
        $this->sections[$section][] = $content;
    }

    /**
     * Save the current section content as a parent section.
     * 
     * @param string $section The name of the section
     */
    private function saveParentSection(string $section) : void {
        $this->parentSections[$section][] = $this->sections[$section];
        $this->sections[$section] = [];
    }

    /**
     * Get the content stack for a section including parent sections.
     * 
     * @param string $section The name of the section
     * @return array The merged array of parent and current section content
     */
    private function getSectionContentStack(string $section) : array {
        return array_merge(
            [$this->sections[$section]],
            array_reverse($this->parentSections[$section]),
        );
    }
}