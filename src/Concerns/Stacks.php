<?php
namespace Selene\Concerns;

trait Stacks {
    /**
     * Stored stacks
     * 
     * @var array
     */
    private array $pushes = [];
    
    /**
     * Stack names
     * 
     * @var array
     */
    private array $pushStack = [];

    /**
     * Define a stack
     * If content is provided, it's stored directly otherwise starts output buffering
     * 
     * @param string $stack
     * @param string $content
     */
    public function push(string $stack, ?string $content = null) : void {
        if (!isset($this->pushes[$stack])) {
            $this->initPush($stack);
        }

        if ($content !== null) {
            $this->extendPush($stack, $content);
        } else {
            $this->pushStack[] = $stack;
            ob_start();
        }
    }
    
    /**
     * End the current push and store its buffered content.
     * 
     * @throws \InvalidArgumentException if the push is not opened
     */
    public function endPush() : void {
        if (empty($this->pushStack)) {
            throw new \InvalidArgumentException('Cannot end a push without opening one');
        }

        $stack = array_pop($this->pushStack);
        $this->extendPush($stack, ob_get_clean());
    }

    /**
     * Define a stack
     * If content is provided, it's stored directly otherwise starts output buffering
     * 
     * @see push()
     * @param string $stack
     * @param string $content
     */
    public function prepend(string $stack, ?string $content = null) : void {
        if (!isset($this->pushes[$stack])) {
            $this->initPush($stack);
        }
        
        if ($content !== null) {
            $this->extendPush($stack, $content);
        } else {
            $this->pushStack[] = $stack;
            ob_start();
        }
    }

    /**
     * End the current prepend and store its buffered content.
     * 
     * @throws \InvalidArgumentException if the prepend is not opened
     */
    public function endPrepend() : void {
        if (empty($this->pushStack)) {
            throw new \InvalidArgumentException('Cannot end a prepend without opening one');
        }

        $stack = array_pop($this->pushStack);
        $this->extendPrepend($stack, ob_get_clean());
    }

    /**
     * Yield the content of a stack
     * 
     * @param string $stack
     * @return string
     */
    public function yieldStack(string $stack) : string {
        return $this->pushes[$stack] ?? '';
    }
    
    /**
     * Append content to a stack
     * 
     * @param string $stack
     * @param string $content
     */
    private function extendPush(string $stack, ?string $content) : void {
        $this->pushes[$stack] .= $content;
    }

    /**
     * Prepend content to a stack
     * 
     * @param string $stack
     * @param string $content
     */
    private function extendPrepend(string $stack, ?string $content) : void {
        $this->pushes[$stack] = $content . $this->pushes[$stack];
    }

    /**
     * Initialize a new push with an empty string.
     * 
     * @param string $stack The name of the push to initialize
     */
    private function initPush(string $stack) : void {
        $this->pushes[$stack] = '';
    }
}