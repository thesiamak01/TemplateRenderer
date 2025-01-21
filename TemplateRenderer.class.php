<?php

/**
 * TemplateRenderer
 *
 * A simple PHP-based template engine that processes various custom tags,
 * such as variables, blocks, PHP code, includes, and conditions, within template strings.
 *
 * Author: Siamak Yousefi
 *
 * Email: syj2001ard@gmail.com
 */

class TemplateRenderer
{
    /**
     * Class responsible for handling tag processing configuration.
     */
    private array $data = []; // Stores the data to be used during tag processing.

    /**
     * @var bool $allowReplaceSimpleTags
     * Determines if simple tags are allowed to be replaced during processing.
     */
    private bool $allowReplaceSimpleTags = true;

    /**
     * @var bool $allowProcessBlocks
     * Indicates whether block-level tag processing is enabled.
     */
    private bool $allowProcessBlocks = true;

    /**
     * @var bool $allowProcessPhpTags
     * Specifies if PHP tags are allowed to be processed.
     */
    private bool $allowProcessPhpTags = true;

    /**
     * @var bool $allowProcessIncludeTags
     * Determines if include tags are permitted to be processed.
     */
    private bool $allowProcessIncludeTags = true;

    /**
     * @var bool $allowProcessConditions
     * Indicates whether conditional tag processing is enabled.
     */
    private bool $allowProcessConditions = true;

    /**
     * Assigns a value to a specified key in the data array.
     *
     * @param string $key The key to which the value should be assigned.
     * @param mixed $value The value to assign to the specified key.
     *
     * @return void
     */
    public function assign(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
    }

    /**
     * Renders a template by processing various types of tags based on the enabled options.
     *
     * The method sequentially processes the following:
     * - Simple tags (e.g., [tag::variable])
     * - Block tags (e.g., [tag::block--Block])
     * - PHP code tags (e.g., [tag::php]...[/tag::php])
     * - Include tags for template inclusion
     * - Conditional tags (e.g., [tag::if($auth === true)])
     *
     * Each type of tag processing can be enabled or disabled using the respective flags.
     *
     * @param string $template The template string to be processed.
     *
     * @return string The processed template string with tags replaced or evaluated.
     */
    public function render(string $template): string
    {
        // Replace simple tags first (e.g., [tag::variable])
        if ($this->allowReplaceSimpleTags)
            $template = $this->replaceSimpleTags($template);

        // Process blocks (e.g., [tag::block--Block])
        if ($this->allowProcessBlocks)
            $template = $this->processBlocks($template);

        // Process PHP code (e.g., [tag::php]...[/tag::php])
        if ($this->allowProcessPhpTags)
            $template = $this->processPhpTags($template);

        // Process include tags
        if ($this->allowProcessIncludeTags)
            $template = $this->processIncludeTags($template);

        // Process conditions (e.g., [tag::if($auth === true)])
        if ($this->allowProcessConditions)
            $template = $this->processConditions($template);

        return $template;
    }

    /**
     * Replaces simple tags in the template with corresponding values from the data array.
     *
     * A simple tag is in the format [tag::$key], where `$key` is a key in the data array.
     * If the value associated with the key is an array, it will be encoded as JSON.
     * Otherwise, the `getReplacement` method is used to obtain the replacement value.
     *
     * @param string $template The template string containing simple tags to be replaced.
     *
     * @return string The template with the simple tags replaced by their corresponding values.
     */
    private function replaceSimpleTags(string $template): string
    {
        foreach ($this->data as $key => $value) {
            $replacement = is_array($value) ? json_encode($value) : $this->getReplacement($key, $value);
            $template = preg_replace("/\[tag::$key]/", $replacement, $template);
        }
        return $template;
    }

    /**
     * Returns the replacement string for a given key and value.
     *
     * If the value is a string, it checks if it is HTML. If so, it returns it as-is;
     * otherwise, it escapes the HTML characters. Non-string values are cast to strings.
     *
     * @param string $key The key of the tag to be replaced.
     * @param mixed $value The value to replace the tag with.
     *
     * @return string The replacement string.
     */
    private function getReplacement(string $key, mixed $value): string
    {
        if (is_string($value)) {
            return $this->isHtml($value) ? $value : $this->escapeHtml($value);
        }
        return (string)$value;
    }

    /**
     * Checks if a given string contains HTML tags.
     *
     * This is used to determine whether to escape HTML characters in the string or not.
     *
     * @param string $string The string to check.
     *
     * @return bool True if the string contains HTML tags, false otherwise.
     */
    private function isHtml(string $string): bool
    {
        return preg_match('/<.*?>/', $string) === 1;
    }

    /**
     * Escapes HTML characters in a given string.
     *
     * This function converts special characters in the string to their HTML entities,
     * making the string safe for output in an HTML context.
     *
     * @param string $value The string to escape.
     *
     * @return string The HTML-escaped string.
     */
    private function escapeHtml(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Processes block-level tags within the template.
     *
     * A block is defined by the format [tag::block--Block]...[/tag::block--Block]. The content
     * inside the block is processed by the `handleBlock` method, which determines whether
     * to process the block data or leave it as raw content.
     *
     * @param string $template The template string containing block tags.
     *
     * @return string The processed template with block tags handled.
     */
    private function processBlocks(string $template): string
    {
        return preg_replace_callback('/\[tag::(\w+)--Block](.*?)\[\/tag::\1--Block]/s', function ($matches) {
            return $this->handleBlock($matches[1], $matches[2]);
        }, $template);
    }

    /**
     * Handles a single block of content by checking if the block has associated data.
     *
     * If data is found for the block, it processes the inner tags within the block content.
     * If no data is found, it returns the raw block content as is.
     *
     * @param string $blockName The name of the block.
     * @param string $blockContent The content of the block.
     *
     * @return string The processed or raw block content.
     */
    private function handleBlock(string $blockName, string $blockContent): string
    {
        return $this->hasBlockData($blockName)
            ? $this->processInnerTags($blockContent, $blockName)
            : $this->getRawBlock($blockName, $blockContent);
    }

    /**
     * Checks if a block has data associated with it.
     *
     * This checks whether the block's name exists in the data array, whether the data is an
     * array, and whether the array is not empty.
     *
     * @param string $blockName The name of the block to check.
     *
     * @return bool True if the block has data, false otherwise.
     */
    private function hasBlockData(string $blockName): bool
    {
        return isset($this->data[$blockName]) && is_array($this->data[$blockName]) && !empty($this->data[$blockName]);
    }

    /**
     * Returns the raw block content without processing if no block data is found.
     *
     * This function returns the block content wrapped in its original tag format if no data
     * is found to process it.
     *
     * @param string $blockName The name of the block.
     * @param string $blockContent The content of the block.
     *
     * @return string The raw block content.
     */
    private function getRawBlock(string $blockName, string $blockContent): string
    {
        return "[tag::{$blockName}--Block] " . $blockContent . " [/tag::{$blockName}--Block]";
    }

    /**
     * Processes inner tags within a block content, specifically loop tags.
     *
     * The content of the block is processed by checking for loop tags (e.g., [tag::Loop]...[/tag::Loop]),
     * and if found, it processes the loop items using the provided block name.
     *
     * @param string $content The content of the block.
     * @param string $blockName The name of the block containing the loop.
     *
     * @return string The processed content with inner tags replaced.
     */
    private function processInnerTags(string $content, string $blockName): string
    {
        return preg_replace_callback('/\[tag::(\w+)--Loop](.*?)\[\/tag::\1--Loop]/s', function ($matches) use ($blockName) {
            return $this->handleInnerTag($matches[1], $matches[2], $blockName);
        }, $content);
    }

    /**
     * Handles a single inner tag (e.g., a loop tag) by checking if the tag matches
     * the block name and if data is available for that tag.
     *
     * If data is found for the tag, it processes the tag as an array tag.
     * Otherwise, it returns an empty string.
     *
     * @param string $tagName The name of the inner tag.
     * @param string $tagContent The content of the inner tag.
     * @param string $blockName The name of the block the tag belongs to.
     *
     * @return string The processed tag content or an empty string if no data is found.
     */
    private function handleInnerTag(string $tagName, string $tagContent, string $blockName): string
    {
        if ($tagName === $blockName && isset($this->data[$tagName]) && is_array($this->data[$tagName])) {
            return $this->processArrayTag($this->data[$tagName], $tagContent);
        }
        return '';
    }

    /**
     * Processes an array of items and replaces the array tag with the corresponding content.
     *
     * This method loops through each item in the array and processes it by replacing the tags
     * in the content with the corresponding values from the item.
     *
     * @param array $items The array of items to process.
     * @param string $content The content containing tags to replace.
     *
     * @return string The rendered content with array tags replaced.
     */
    private function processArrayTag(array $items, string $content): string
    {
        $renderedContent = '';
        foreach ($items as $itemData) {
            $itemContent = $content;
            foreach ($itemData as $key => $value) {
                $itemContent = preg_replace("/\[tag::$key]/", $this->escapeHtml($value), $itemContent);
            }
            $renderedContent .= $itemContent;
        }
        return $renderedContent;
    }

    /**
     * Processes PHP code within the template, enclosed in [tag::php]...[/tag::php].
     * The PHP code is executed and its output is captured and inserted into the template.
     *
     * If an error occurs while executing the PHP code, an error message is returned.
     *
     * @param string $template The template string containing PHP code tags.
     *
     * @return string The template with PHP code processed and output inserted.
     */
    private function processPhpTags(string $template): string
    {
        return preg_replace_callback('/\[tag::php](.*?)\[\/tag::php]/s', function ($matches) {
            $phpCode = trim($matches[1]);

            // Capture the output of the PHP code execution
            ob_start();
            try {
                eval($phpCode); // Execute the PHP code
            } catch (\Throwable $e) {
                ob_end_clean();
                return "Error in PHP code: " . htmlspecialchars($e->getMessage());
            }
            return ob_get_clean();
        }, $template);
    }

    /**
     * Processes include tags within the template, enclosed in [tag::include(...)].
     * If the specified file exists and is readable, it is included in the template.
     *
     * If the file cannot be included due to an error, an error message is returned.
     *
     * @param string $template The template string containing include tags.
     *
     * @return string The template with include tags processed and file content inserted.
     */
    private function processIncludeTags(string $template): string
    {
        return preg_replace_callback('/\[tag::include\((.*?)\)]/', function ($matches) {
            $filePath = trim($matches[1], "\"'");
            if (file_exists($filePath) && is_readable($filePath)) {
                ob_start();
                try {
                    include_once $filePath;
                } catch (\Throwable $e) {
                    ob_end_clean();
                    return "Error including file: " . htmlspecialchars($e->getMessage());
                }
                return ob_get_clean();
            }
            return "Error: File not found or not readable: " . htmlspecialchars($filePath);
        }, $template);
    }

    /**
     * Processes condition tags within the template, enclosed in [tag::if(...)]...[/tag::endif].
     * The conditions are evaluated, and the corresponding content is returned based on the result.
     *
     * The syntax supports [tag::elseif(...)] and [tag::else] tags for alternative conditions.
     *
     * @param string $template The template string containing condition tags.
     *
     * @return string The template with condition tags processed.
     */
    private function processConditions(string $template): string
    {
        return preg_replace_callback(
            '/\[tag::if\((.*?)\)](.*?)((?:\[tag::elseif\((.*?)\)](.*?)\)?)*)\[tag::else](.*?)\[tag::endif]/s',
            function ($matches) {
                $ifCondition = trim($matches[1]);
                $ifContent   = trim($matches[2]);
                $elseifBlock = isset($matches[3]) ? trim($matches[3]) : '';
                $elseContent = trim($matches[6]);

                if ($this->evaluateCondition($ifCondition)) {
                    return $ifContent;
                }

                if (!empty($elseifBlock) && preg_match_all('/\[tag::elseif\((.*?)\)](.*)/s', $elseifBlock, $elseifMatches, PREG_SET_ORDER)) {
                    foreach ($elseifMatches as $elseifMatch) {
                        $elseifCondition = trim($elseifMatch[1]);
                        $elseifContent   = trim($elseifMatch[2]);

                        if ($this->evaluateCondition($elseifCondition)) {
                            return $elseifContent;
                        }
                    }
                }

                return $elseContent;
            },
            $template
        );
    }

    /**
     * Evaluates a condition by replacing variables with corresponding values from the data array.
     * The condition is then evaluated using eval(). If the condition is valid, it returns the result.
     *
     * @param string $condition The condition string to evaluate.
     *
     * @return bool The result of the evaluated condition.
     */
    private function evaluateCondition(string $condition): bool
    {
        foreach ($this->data as $key => $value) {
            if (is_array($value)) {
                $condition = str_replace('$' . $key, 'null', $condition);
            } elseif (is_numeric($value)) {
                $condition = str_replace('$' . $key, $value, $condition);
            } elseif (is_bool($value)) {
                $condition = str_replace('$' . $key, $value ? 'true' : 'false', $condition);
            } else {
                $condition = str_replace('$' . $key, '"' . addslashes((string)$value) . '"', $condition);
            }
        }

        try {
            $result = eval("return ($condition);");
            return (bool)$result;
        } catch (\Throwable $e) {
            return false;
        }
    }

}
