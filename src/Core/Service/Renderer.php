<?php
namespace App\Core\Service;

include_once '../src/Core/KernelServiceInterface.php';
include_once '../src/Core/Service/Renderer/TemplatesCachingService.php';

use App\Core\KernelServiceInterface;
use Throwable;
use RuntimeException;

class Renderer implements KernelServiceInterface
{
    private string $templateDir = '../templates/';

    private array $sectionsStack = [];
    private array $currentSections = [];
    private ?string $currentSection = null;
    private ?string $layout = null;
    
    public function __construct(
        private TemplatesCachingService $caching
    )
    {}

    public function enableCaching(bool $flag): void
    {
        $this->caching->enableCaching($flag);
    }

    public function setCacheConfig(string $templatePath, bool $enabled, ?int $ttl = null): void
    {
        $this->caching->setCacheConfig($templatePath, $enabled, $ttl);
    }
    
    public function render(string $templatePath, array $data = []): string
    {
        $cacheFilePath = $this->caching->getCacheFilePath($templatePath, $data);

        if ($content = $this->caching->extractFromCache($templatePath, $cacheFilePath)) {
            return $content;
        }

        $content = $this->compileTemplate($templatePath, $data);

        $this->caching->tryToCache($templatePath, $cacheFilePath, $content);

        return $content;
    }

    private function compileTemplate(string $templatePath, array $data): string
    {
        $this->sectionsStack[] = $this->currentSections;
        $this->currentSections = [];
        $this->currentSection = null;
        $this->layout = null;
        
        $fullPath = $this->templateDir . $templatePath;
        
        if (!file_exists($fullPath)) {
            throw new RuntimeException("Не найден файл шаблона: " . $fullPath);
        }

        // Рендерим шаблон
        $content = $this->renderTemplate($fullPath, $data);
        
        // Если есть layout, рендерим его
        if ($this->layout) {
            $layoutPath = $this->templateDir . $this->layout;
            $content = $this->compileTemplate($layoutPath, array_merge($data, ['content' => $content]));
        }

        // Восстанавливаем предыдущие секции
        $this->currentSections = array_pop($this->sectionsStack);
        
        return $content;
    }
    
    protected function renderTemplate(string $templatePath, array $data): string
    {
        extract($data, EXTR_SKIP);
        ob_start();
        
        try {
            include $templatePath;
        } catch (Throwable $e) {
            ob_end_clean();
            throw new RuntimeException("Ошибка рендеринга шаблона: " . $e->getMessage());
        }
        
        return ob_get_clean();
    }
    
    public function escape(string|array $value)
    {
        if (is_array($value)) {
            return array_map([$this, 'escapeString'], $value);
        }
        return $this->escapeString($value);
    }
    
    public function extend(string $layout): void
    {
        $this->layout = $layout;
    }
    
    public function setLayout(string $layout): void
    {
        $this->layout = $layout;
    }
    
    public function startSection(string $name): void
    {
        $this->currentSection = $name;
        ob_start();
    }
    
    public function endSection(): void
    {
        if ($this->currentSection) {
            $this->currentSections[$this->currentSection] = ob_get_clean();
            $this->currentSection = null;
        }
    }
    
    public function section(string $name): string
    {
        // Ищем секцию в текущем контексте
        if (isset($this->currentSections[$name])) {
            return $this->currentSections[$name];
        }
        
        // Ищем в родительских контекстах
        foreach (array_reverse($this->sectionsStack) as $sections) {
            if (isset($sections[$name])) {
                return $sections[$name];
            }
        }
        
        return '';
    }

    public function includeComponent(string $componentPath, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        include $this->templateDir . $componentPath;
    }

    public function getStatusColor(string $status, array $statusConfig): string
    {
        // Проверяем все типы статусов
        foreach ($statusConfig as $typeConfig) {
            if (isset($typeConfig[$status])) {
                return $typeConfig[$status]['color'];
            }
        }
        
        return '#858796'; // цвет по умолчанию
    }

    public function getStatusText(string $status, array $statusConfig): string
    {
        // Проверяем все типы статусов
        foreach ($statusConfig as $typeConfig) {
            if (isset($typeConfig[$status])) {
                return $typeConfig[$status]['text'];
            }
        }
        
        return $status; // исходное название, если не найдено
    }

    private function escapeString(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }
}