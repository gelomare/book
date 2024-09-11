<?php

/**
 * Парсинг FB2 или EPUB файлов
 *
 * @param string $filepath Путь к файлу
 * @return string Содержимое книги или сообщение об ошибке
 */
function parseFile($filepath)
{
    if (!file_exists($filepath)) {
        return 'Файл не найден.';
    }

    $extension = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
    switch ($extension) {
        case 'fb2':
            return parseFB2File($filepath);
        case 'epub':
            return parseEPUBFile($filepath);
        default:
            return 'Недопустимый формат файла.';
    }
}

/**
 * Парсинг FB2 файла
 *
 * @param string $filepath Путь к FB2 файлу
 * @return string Содержимое книги или сообщение об ошибке
 */
function parseFB2File($filepath)
{
    libxml_use_internal_errors(true);
    $xml = simplexml_load_file($filepath);
    if (!$xml) {
        $errors = libxml_get_errors();
        libxml_clear_errors();
        $errorMessages = implode('<br>', array_map(fn($error) => $error->message, $errors));
        return 'Ошибка при загрузке FB2 файла: ' . $errorMessages;
    }

    $content = '';

    // Обработка метаданных книги
    if (isset($xml->description)) {
        $content .= '<div class="metadata">';
        $content .= formatTitle($xml);
        $content .= formatAuthors($xml);
        $content .= '</div>';
    }

    // Обработка тела текста книги
    foreach ($xml->children() as $body) {
        if ($body->getName() === 'body') {
            foreach ($body->section as $section) {
                $content .= '<h2>' . (isset($section->title) ? getSectionTitle($section->title) : 'Без названия') . '</h2>';
                foreach ($section->children() as $element) {
                    $content .= processElement($element, $xml);
                }
            }
        }
    }

    return $content;
}

/**
 * Форматирование заголовка книги
 *
 * @param SimpleXMLElement $xml XML элемент
 * @return string HTML строка с заголовком
 */
function formatTitle($xml)
{
    if (isset($xml->description->title_info->book_title)) {
        return '<h1>' . htmlspecialchars((string)$xml->description->title_info->book_title, ENT_QUOTES, 'UTF-8') . '</h1>';
    }
    return '';
}

/**
 * Форматирование авторов книги
 *
 * @param SimpleXMLElement $xml XML элемент
 * @return string HTML строка с авторами
 */
function formatAuthors($xml)
{
    $authorsContent = '';
    if (isset($xml->description->title_info->author)) {
        foreach ($xml->description->title_info->author as $author) {
            $fullName = trim(htmlspecialchars((string)$author->first_name . ' ' . $author->last_name, ENT_QUOTES, 'UTF-8'));
            if ($fullName) {
                $authorsContent .= '<p><strong>Автор:</strong> ' . $fullName . '</p>';
            }
        }
    }
    return $authorsContent;
}

/**
 * Получение заголовка секции
 *
 * @param SimpleXMLElement $title XML элемент заголовка
 * @return string Заголовок секции
 */
function getSectionTitle($title)
{
    return trim(htmlspecialchars((string)$title->p, ENT_QUOTES, 'UTF-8'));
}

/**
 * Обработка элементов книги
 *
 * @param SimpleXMLElement $element XML элемент
 * @param SimpleXMLElement $xml Полный XML элемент книги
 * @return string HTML строка с элементами
 */
function processElement($element, $xml)
{
    $content = '';
    switch ($element->getName()) {
        case 'p':
            $content .= '<p>' . htmlspecialchars((string)$element, ENT_QUOTES, 'UTF-8') . '</p>';
            break;
        case 'subtitle':
            $content .= '<h3>' . htmlspecialchars((string)$element, ENT_QUOTES, 'UTF-8') . '</h3>';
            break;
        case 'poem':
            $content .= processPoem($element);
            break;
        case 'cite':
            $content .= processCite($element);
            break;
        case 'table':
            $content .= processTable($element);
            break;
        case 'image':
            $content .= processImage($element, $xml);
            break;
        case 'emphasis':
            $content .= '<em>' . htmlspecialchars((string)$element, ENT_QUOTES, 'UTF-8') . '</em>';
            break;
        case 'strong':
            $content .= '<strong>' . htmlspecialchars((string)$element, ENT_QUOTES, 'UTF-8') . '</strong>';
            break;
        case 'strikethrough':
            $content .= '<del>' . htmlspecialchars((string)$element, ENT_QUOTES, 'UTF-8') . '</del>';
            break;
        case 'empty-line':
            $content .= '<br/>'; // Для пустых строк
            break;
        default:
            // Обработка других элементов по необходимости
            break;
    }
    return $content;
}

/**
 * Обработка стихотворения
 *
 * @param SimpleXMLElement $element XML элемент стихотворения
 * @return string HTML строка с стихотворением
 */
function processPoem($element)
{
    $content = '<div class="poem">';
    if (isset($element->title->p)) {
        $content .= '<h3>' . htmlspecialchars((string)$element->title->p, ENT_QUOTES, 'UTF-8') . '</h3>';
    }
    foreach ($element->stanza as $stanza) {
        $content .= '<div class="stanza">';
        foreach ($stanza->v as $v) {
            $content .= '<p>' . htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8') . '</p>';
        }
        $content .= '</div>';
    }
    $content .= '</div>';
    return $content;
}

/**
 * Обработка цитаты
 *
 * @param SimpleXMLElement $element XML элемент цитаты
 * @return string HTML строка с цитатой
 */
function processCite($element)
{
    $content = '<blockquote>';
    foreach ($element->p as $citeParagraph) {
        $content .= '<p>' . htmlspecialchars((string)$citeParagraph, ENT_QUOTES, 'UTF-8') . '</p>';
    }
    $content .= '</blockquote>';
    return $content;
}

/**
 * Обработка таблицы
 *
 * @param SimpleXMLElement $element XML элемент таблицы
 * @return string HTML строка с таблицей
 */
function processTable($element)
{
    $content = '<table>';
    foreach ($element->tr as $row) {
        $content .= '<tr>';
        foreach ($row->td as $cell) {
            $content .= '<td>' . htmlspecialchars((string)$cell, ENT_QUOTES, 'UTF-8') . '</td>';
        }
        $content .= '</tr>';
    }
    $content .= '</table>';
    return $content;
}

/**
 * Обработка изображения
 *
 * @param SimpleXMLElement $element XML элемент изображения
 * @param SimpleXMLElement $xml Полный XML элемент книги
 * @return string HTML строка с изображением
 */
function processImage($element, $xml)
{
    $content = '';
    $href = (string)$element['l:href'];
    if (startsWith($href, '#')) {
        $imageId = substr($href, 1);
        $binary = $xml->xpath("//binary[@id='$imageId']");
        if ($binary) {
            $content .= '<img src="data:image/*;base64,' . htmlspecialchars((string)$binary[0], ENT_QUOTES, 'UTF-8') . '"/>';
        }
    }
    return $content;
}

/**
 * Проверка, начинается ли строка с заданного префикса
 *
 * @param string $string Строка для проверки
 * @param string $startString Префикс
 * @return bool true, если строка начинается с префикса
 */
function startsWith($string, $startString)
{
    return (substr($string, 0, strlen($startString)) === $startString);
}

/**
 * Парсинг EPUB файла
 *
 * @param string $filepath Путь к EPUB файлу
 * @return string Содержимое книги или сообщение об ошибке
 */
function parseEPUBFile($filepath)
{
    $zip = new ZipArchive();
    if ($zip->open($filepath) === TRUE) {
        // Проверяем наличие файла content.opf в архиве
        $opfFiles = ['content.opf', 'OPS/content.opf', 'OEBPS/content.opf'];
        $opfContent = '';
        foreach ($opfFiles as $contentFile) {
            if ($zip->locateName($contentFile) !== false) {
                $opfContent = $zip->getFromName($contentFile);
                break;
            }
        }

        if (empty($opfContent)) {
            return 'Ошибка: файл content.opf не найден.';
        }

        $opfXml = simplexml_load_string($opfContent);
        if (!$opfXml) {
            return 'Ошибка при загрузке content.opf файла.';
        }

        // Извлекаем файлы с содержимым
        $htmlFiles = [];
        foreach ($opfXml->manifest->item as $item) {
            if ((string)$item['media-type'] === 'application/xhtml+xml') {
                $htmlFiles[] = (string)$item['href'];
            }
        }

        // Проверяем, что файлы действительно существуют
        $content = '';
        foreach ($htmlFiles as $htmlFile) {
            if ($zip->locateName($htmlFile) !== false) {
                $htmlContent = $zip->getFromName($htmlFile);
                if ($htmlContent !== false) {
                    $content .= processHTMLContent($htmlContent);
                } else {
                    return "Ошибка: не удалось извлечь файл $htmlFile.";
                }
            } else {
                return "Ошибка: файл $htmlFile не найден в архиве.";
            }
        }

        $zip->close();
        return $content;
    } else {
        return 'Ошибка при открытии EPUB файла.';
    }
}

/**
 * Обработка HTML содержимого
 *
 * @param string $htmlContent Содержимое HTML
 * @return string Отформатированное содержимое
 */
function processHTMLContent($htmlContent)
{
    // Преобразование HTML в нужный формат
    $dom = new DOMDocument();
    @$dom->loadHTML($htmlContent);
    $xpath = new DOMXPath($dom);

    $content = '';
    foreach ($xpath->query('//body/*') as $node) {
        $content .= $dom->saveHTML($node);
    }

    return $content;
}

?>