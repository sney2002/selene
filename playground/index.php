<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Selene\Visitor\PhpTransformVisitor;
use Selene\Parser;

$template = <<<'TEMPLATE'
{{-- This is a comment --}}
{!! $html !!}
@forelse($emptyArray as $item)
    <p>{{ $item }}</p>
    @empty($emptyVariable)
        <p>Forelse Empty</p>
    @endempty
@empty
    <p>Forelse Empty</p>
@endforelse

<ul>
    @foreach ($items as $item)
        <li>{{ $item }}</li>
    @endforeach
</ul>

@switch($variable)
    @case(1)
        <p>Switch One</p>
    @break
    @case(2)
        <p>Switch Two</p>
    @break
    @default
        <p>Switch Default</p>
@endswitch

<button @disabled(count($items) > 0)>Disabled</button>
<button @disabled(count($items) === 0)>Enabled</button>

@style([
    "background-color: red",
    "font-weight: bold" => count($items) > 0
])

@if($variable)
    <p>Variable is true and its value is {{ $variable }}</p>
@else
    <p>Variable is false</p>
@endif

@empty($emptyVariable)
    <p>Variable is empty and its value is {{ $emptyVariable }}</p>
@endempty

@isset($undefinedVariable)
    <p>Variable exists and its value is {{ $undefinedVariable }}</p>
@else
    <p>Variable doesn't exist</p>
@endisset
TEMPLATE;

function view(string $template, array $data = []) {
    extract($data);

    $parser = new Parser($template);
    $nodes = $parser->parse();
    $visitor = new PhpTransformVisitor();

    $result = $visitor->compile($nodes);

    file_put_contents(__DIR__ . '/output.php', $result);

    ob_start();
    require __DIR__ . '/output.php';
    $output = ob_get_clean();

    unlink(__DIR__ . '/output.php');

    return $output;
}

function e($value) {
    if (is_null($value)) {
        return 'null';
    }

    return htmlentities($value);
}

$parser = new Parser($template);
$nodes = $parser->parse();
$visitor = new PhpTransformVisitor();

$result = $visitor->compile($nodes);
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.11.1/styles/default.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.11.1/highlight.min.js"></script>
<script>
    hljs.registerLanguage("blade",(()=>{"use strict";return e=>({name:"Blade",
case_insensitive:!0,subLanguage:"xml",contains:[e.COMMENT(/\{\{--/,/--\}\}/),{
className:"template-variable",begin:/\{\{/,starts:{end:/\}\}/,returnEnd:!0,
subLanguage:"php"}},{className:"template-variable",begin:/\}\}/},{
className:"template-variable",begin:/\{!!/,starts:{end:/!!\}/,returnEnd:!0,
subLanguage:"php"}},{className:"template-variable",begin:/!!\}/},{
className:"template-tag",begin:/@php/,starts:{end:/@endphp/,returnEnd:!0,
subLanguage:"php"},relevance:10},{begin:/@[\w]+/,end:/[\W]/,excludeEnd:!0,
className:"template-tag"}]})})());
</script>

<style>
    pre {
        background-color: #f0f0f0;
        padding: 10px;
        border-radius: 5px;
    }
</style>
<div style="display: flex; gap: 20px">
    <div style="flex: 1">
        <h2>Template</h2>
        <pre>
            <code class="language-blade"><?php echo htmlspecialchars($template); ?></code>
        </pre>
    </div>
    <div style="flex: 1">
        <h2>Result</h2>
        <pre>
            <code class="language-php"><?php echo htmlspecialchars($result); ?></code>
        </pre>
    </div>
</div>
<h1>Output</h1>
<script>
    hljs.highlightAll();
</script>
<?php echo view($template, [
    'variable' => 1,
    'emptyVariable' => null,
    'emptyArray' => [],
    'html' => '<h2 style="border: 2px dashed red;">Unescaped HTML</h2>',
    'items' => ['item 1', 'item 2', 'item 3'],
]); ?>