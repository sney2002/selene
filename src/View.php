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
     * Stack of section names
     * 
     * @var array
     */
    private array $sectionStack = [];

    /**
     * Define a section with content.
     * 
     * If content is provided as a string, it's stored directly.
     * If content is null, starts output buffering and pushes the section name
     * to the stack for later capture with endSection().
     * 
     * @param string $section The name of the section
     * @param string|null $content The content to store, or null to start buffering
     */
    public function section(string $section, ?string $content = null) : void {
        if ($content !== null) {
            $this->sections[$section] = $content;
            return;
        }
        
        $this->sectionStack[] = $section;
        ob_start();
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
        $this->sections[$section] = ob_get_clean();
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
     * Get the content of a section by name.
     * 
     * @param string $section The name of the section to retrieve
     * @return string The section content or empty string if not found
     */
    public function yield(string $section) : string {
        return $this->sections[$section] ?? '';
    }
}