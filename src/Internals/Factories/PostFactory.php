<?php

namespace NickMous\Binsta\Internals\Factories;

use DateTime;
use NickMous\Binsta\Entities\Post;

class PostFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        $programmingLanguages = [
            'javascript', 'typescript', 'php', 'python', 'java', 'html', 'css', 
            'json', 'bash', 'shell', 'c', 'cpp', 'csharp', 'go', 'rust', 
            'ruby', 'kotlin', 'swift', 'sql', 'yaml', 'xml', 'vue', 'jsx', 'tsx'
        ];

        $language = $this->faker()->randomElement($programmingLanguages);
        
        return [
            'title' => $this->generateTitle($language),
            'description' => $this->generateDescription($language),
            'code' => $this->generateCode($language),
            'programmingLanguage' => $language,
            'userId' => $this->faker()->numberBetween(1, 15), // Assuming 15 users from UserSeeder
            'createdAt' => $this->faker()->dateTimeBetween('-3 months', 'now'),
            'updatedAt' => new DateTime(),
        ];
    }

    public function modelClass(): string
    {
        return Post::class;
    }

    private function generateTitle(string $language): string
    {
        $patterns = [
            'How to {} in {}',
            '{} {} Implementation',
            'Simple {} with {}',
            '{} {} Example',
            'Building a {} using {}',
            '{} {} Tutorial',
            'Quick {} {} Snippet'
        ];

        $actions = ['create', 'build', 'implement', 'generate', 'parse', 'validate', 'process', 'handle'];
        $concepts = ['function', 'class', 'component', 'service', 'helper', 'utility', 'module', 'handler'];
        
        $pattern = $this->faker()->randomElement($patterns);
        $action = $this->faker()->randomElement($actions);
        $concept = $this->faker()->randomElement($concepts);
        
        return str_replace(['{}', '{}'], [ucfirst($action) . ' ' . $concept, ucfirst($language)], $pattern);
    }

    private function generateDescription(string $language): string
    {
        $templates = [
            'This {} shows how to {} in {}. ' . $this->faker()->sentence(),
            'A simple {} example that demonstrates {} concepts. ' . $this->faker()->sentence(),
            'Learn how to {} with this {} code snippet. ' . $this->faker()->sentence(),
            'Practical {} implementation for {}. ' . $this->faker()->sentence(),
        ];

        $types = ['snippet', 'example', 'function', 'implementation', 'solution'];
        $actions = ['handle data', 'process arrays', 'manage state', 'validate input', 'format output'];
        
        $template = $this->faker()->randomElement($templates);
        $type = $this->faker()->randomElement($types);
        $action = $this->faker()->randomElement($actions);
        
        return str_replace(['{}', '{}', '{}'], [$type, $action, $language], $template);
    }

    private function generateCode(string $language): string
    {
        return match($language) {
            'javascript', 'typescript' => $this->generateJavaScriptCode(),
            'php' => $this->generatePhpCode(),
            'python' => $this->generatePythonCode(),
            'java' => $this->generateJavaCode(),
            'css' => $this->generateCssCode(),
            'html' => $this->generateHtmlCode(),
            'sql' => $this->generateSqlCode(),
            'json' => $this->generateJsonCode(),
            'bash', 'shell' => $this->generateBashCode(),
            default => $this->generateGenericCode($language)
        };
    }

    private function generateJavaScriptCode(): string
    {
        $functionNames = ['calculateTotal', 'formatData', 'validateInput', 'processArray', 'handleEvent'];
        $varNames = ['data', 'items', 'result', 'value', 'config'];
        
        $funcName = $this->faker()->randomElement($functionNames);
        $varName = $this->faker()->randomElement($varNames);
        $number = $this->faker()->numberBetween(1, 100);
        
        return "function {$funcName}({$varName}) {\n" .
               "    const result = {$varName}.map(item => item * {$number});\n" .
               "    return result.filter(value => value > 0);\n" .
               "}\n\n" .
               "const {$varName} = [" . implode(', ', $this->faker()->randomElements(range(1, 20), 5)) . "];\n" .
               "console.log({$funcName}({$varName}));";
    }

    private function generatePhpCode(): string
    {
        $classNames = ['DataProcessor', 'UserManager', 'ConfigHandler', 'ApiService'];
        $methodNames = ['process', 'validate', 'transform', 'calculate'];
        
        $className = $this->faker()->randomElement($classNames);
        $methodName = $this->faker()->randomElement($methodNames);
        $varName = $this->faker()->randomElement(['data', 'input', 'value', 'config']);
        
        return "<?php\n\n" .
               "class {$className} {\n" .
               "    public function {$methodName}(\${$varName}) {\n" .
               "        if (empty(\${$varName})) {\n" .
               "            return null;\n" .
               "        }\n\n" .
               "        return array_map(function(\$item) {\n" .
               "            return strtoupper(\$item);\n" .
               "        }, \${$varName});\n" .
               "    }\n" .
               "}";
    }

    private function generatePythonCode(): string
    {
        $functionNames = ['process_data', 'calculate_sum', 'filter_items', 'format_output'];
        $varNames = ['data', 'items', 'numbers', 'values'];
        
        $funcName = $this->faker()->randomElement($functionNames);
        $varName = $this->faker()->randomElement($varNames);
        
        return "def {$funcName}({$varName}):\n" .
               "    if not {$varName}:\n" .
               "        return []\n" .
               "    \n" .
               "    result = [item for item in {$varName} if item > 0]\n" .
               "    return sorted(result)\n\n" .
               "{$varName} = [" . implode(', ', $this->faker()->randomElements(range(-10, 20), 6)) . "]\n" .
               "print({$funcName}({$varName}))";
    }

    private function generateJavaCode(): string
    {
        $classNames = ['DataProcessor', 'StringUtils', 'Calculator', 'ArrayHelper'];
        $className = $this->faker()->randomElement($classNames);
        
        return "public class {$className} {\n" .
               "    public static void main(String[] args) {\n" .
               "        int[] numbers = {" . implode(', ', $this->faker()->randomElements(range(1, 50), 5)) . "};\n" .
               "        \n" .
               "        for (int num : numbers) {\n" .
               "            System.out.println(\"Number: \" + num);\n" .
               "        }\n" .
               "    }\n" .
               "}";
    }

    private function generateCssCode(): string
    {
        $selectors = ['.card', '.button', '.container', '.header', '.nav'];
        $colors = ['#3498db', '#e74c3c', '#2ecc71', '#f39c12', '#9b59b6'];
        
        $selector = $this->faker()->randomElement($selectors);
        $color = $this->faker()->randomElement($colors);
        $size = $this->faker()->numberBetween(12, 48);
        
        return "{$selector} {\n" .
               "    background-color: {$color};\n" .
               "    padding: {$size}px;\n" .
               "    border-radius: 8px;\n" .
               "    transition: all 0.3s ease;\n" .
               "}\n\n" .
               "{$selector}:hover {\n" .
               "    transform: scale(1.05);\n" .
               "    opacity: 0.9;\n" .
               "}";
    }

    private function generateHtmlCode(): string
    {
        $titles = $this->faker()->words(3, true);
        $content = $this->faker()->sentence();
        
        return "<!DOCTYPE html>\n" .
               "<html lang=\"en\">\n" .
               "<head>\n" .
               "    <meta charset=\"UTF-8\">\n" .
               "    <title>" . ucwords($titles) . "</title>\n" .
               "</head>\n" .
               "<body>\n" .
               "    <header>\n" .
               "        <h1>" . ucwords($titles) . "</h1>\n" .
               "    </header>\n" .
               "    <main>\n" .
               "        <p>{$content}</p>\n" .
               "    </main>\n" .
               "</body>\n" .
               "</html>";
    }

    private function generateSqlCode(): string
    {
        $tables = ['users', 'posts', 'orders', 'products', 'categories'];
        $table = $this->faker()->randomElement($tables);
        $condition = $this->faker()->numberBetween(1, 100);
        
        return "SELECT *\n" .
               "FROM {$table}\n" .
               "WHERE id > {$condition}\n" .
               "ORDER BY created_at DESC\n" .
               "LIMIT 10;";
    }

    private function generateJsonCode(): string
    {
        return json_encode([
            'name' => $this->faker()->name(),
            'email' => $this->faker()->email(),
            'age' => $this->faker()->numberBetween(18, 80),
            'active' => $this->faker()->boolean(),
            'tags' => $this->faker()->words(3)
        ], JSON_PRETTY_PRINT);
    }

    private function generateBashCode(): string
    {
        $commands = ['ls -la', 'grep -r "pattern"', 'find . -name "*.php"', 'chmod +x script.sh'];
        $command = $this->faker()->randomElement($commands);
        
        return "#!/bin/bash\n\n" .
               "# Simple bash script\n" .
               "echo \"Starting script...\"\n\n" .
               "{$command}\n\n" .
               "echo \"Script completed!\"";
    }

    private function generateGenericCode(string $language): string
    {
        return "// {$language} code example\n" .
               $this->faker()->text(150) . "\n\n" .
               "// " . $this->faker()->sentence();
    }

    public function withLanguage(string $language): static
    {
        return $this->state([
            'programmingLanguage' => $language,
        ]);
    }

    public function withUser(int $userId): static
    {
        return $this->state([
            'userId' => $userId,
        ]);
    }

    public function recent(): static
    {
        return $this->state([
            'createdAt' => $this->faker()->dateTimeBetween('-1 week', 'now'),
        ]);
    }
}