<?php

include_once ('TemplateRenderer.class.php');

// Example usage
$renderer = new TemplateRenderer();

$renderer->assign('siteTitle', 'Test WebSite');

$renderer->assign('siteLink', 'http://localhost:8080');

$renderer->assign('BlogPostCategories', [
    ['CategoryName' => 'Cat 1', 'CategoryLink' => 'cat-1', 'CategoryId' => '1'],
    ['CategoryName' => 'Cat 2', 'CategoryLink' => 'cat-2', 'CategoryId' => '2'],
    ['CategoryName' => 'Cat 3', 'CategoryLink' => 'cat-3', 'CategoryId' => '3'],
]);

$renderer->assign('AnotherList', [
    ['ItemName' => 'Item 1', 'ItemLink' => 'item-1', 'ItemId' => '101'],
    ['ItemName' => 'Item 2', 'ItemLink' => 'item-2', 'ItemId' => '102'],
]);

$renderer->assign('PostList', [
    ['PostItemName' => 'Item 1', 'PostItemLink' => 'item-1', 'PostItemId' => '1'],
    ['PostItemName' => 'Item 2', 'PostItemLink' => 'item-2', 'PostItemId' => '2'],
]);

$renderer->assign('auth', false);

$renderer->assign('val1', 0);
$renderer->assign('val2', 0);

$renderer->assign('name', 'reza');

$renderer->assign('form', file_get_contents('form.html'));

// get template from external file
$template = file_get_contents('template.html');

echo $renderer->render($template);
