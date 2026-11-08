<?php

namespace App\Services;

use DOMDocument;
use DOMXPath;
use Illuminate\Validation\ValidationException;
use ZipArchive;

class MemberWorkbook
{
    public const HEADERS = [
        'Nama Lengkap',
        'Email Google',
        'Role',
        'Angkatan',
    ];

    private function xml(string $text): DOMXPath
    {
        if (
            stripos($text, '<!DOCTYPE') !== false
            || stripos($text, '<!ENTITY') !== false
        ) {
            throw new \RuntimeException(
                'XML tidak diperbolehkan.'
            );
        }

        $document = new DOMDocument;

        $previousSetting = libxml_use_internal_errors(true);

        try {
            if (! $document->loadXML($text, LIBXML_NONET)) {
                throw new \RuntimeException('XML rusak.');
            }
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousSetting);
        }

        $xpath = new DOMXPath($document);

        $xpath->registerNamespace(
            's',
            'http://schemas.openxmlformats.org/spreadsheetml/2006/main'
        );

        return $xpath;
    }

    public function read(string $path): array
    {
        $zip = new ZipArchive;

        if ($zip->open($path) !== true) {
            throw ValidationException::withMessages([
                'workbook' => 'File Excel tidak valid.',
            ]);
        }

        try {
            $totalSize = 0;

            if ($zip->numFiles > 200) {
                throw new \RuntimeException(
                    'Terlalu banyak bagian file.'
                );
            }

            for ($index = 0; $index < $zip->numFiles; $index++) {
                $fileInfo = $zip->statIndex($index);

                $totalSize += $fileInfo['size'];

                if ($totalSize > 20 * 1024 * 1024) {
                    throw new \RuntimeException(
                        'Isi file terlalu besar.'
                    );
                }
            }

            $sharedStrings = [];

            $sharedXml = $zip->getFromName(
                'xl/sharedStrings.xml'
            );

            if ($sharedXml !== false) {
                $sharedXPath = $this->xml($sharedXml);

                foreach ($sharedXPath->query('//s:si') as $item) {
                    $value = '';

                    foreach (
                        $sharedXPath->query('.//s:t', $item) as $text
                    ) {
                        $value .= $text->textContent;
                    }

                    $sharedStrings[] = $value;
                }
            }

            $workbookXPath = $this->xml(
                $zip->getFromName('xl/workbook.xml') ?: ''
            );

            $firstSheet = $workbookXPath
                ->query('//s:sheets/s:sheet')
                ->item(0);

            if (! $firstSheet) {
                throw new \RuntimeException(
                    'Sheet tidak ditemukan.'
                );
            }

            $relationshipId = $firstSheet->getAttributeNS(
                'http://schemas.openxmlformats.org/officeDocument/2006/relationships',
                'id'
            );

            $relationshipXPath = $this->xml(
                $zip->getFromName(
                    'xl/_rels/workbook.xml.rels'
                ) ?: ''
            );

            $target = null;

            $relationships = $relationshipXPath->query(
                '//*[local-name()="Relationship"]'
            );

            foreach ($relationships as $relationship) {
                if (
                    $relationship->getAttribute('Id')
                        === $relationshipId
                    && $relationship->getAttribute('TargetMode')
                        !== 'External'
                ) {
                    $target = $relationship->getAttribute(
                        'Target'
                    );
                }
            }

            if (
                ! $target
                || str_contains($target, '..')
                || str_contains($target, ':')
            ) {
                throw new \RuntimeException(
                    'Sheet tidak valid.'
                );
            }

            $target = str_starts_with($target, '/')
                ? ltrim($target, '/')
                : 'xl/'.$target;

            $sheetXPath = $this->xml(
                $zip->getFromName($target) ?: ''
            );

            $rows = [];

            foreach (
                $sheetXPath->query('//s:sheetData/s:row') as $row
            ) {
                if (count($rows) > 1000) {
                    throw new \RuntimeException(
                        'Maksimal 1000 anggota.'
                    );
                }

                $values = ['', '', '', ''];

                foreach (
                    $sheetXPath->query('s:c', $row) as $cell
                ) {
                    if ($sheetXPath->query('s:f', $cell)->length) {
                        throw new \RuntimeException(
                            'Gunakan nilai teks/angka, bukan formula.'
                        );
                    }

                    $reference = $cell->getAttribute('r');

                    if (
                        ! preg_match(
                            '/^([A-Z]+)[0-9]+$/',
                            $reference,
                            $matches
                        )
                    ) {
                        continue;
                    }

                    $column = $matches[1];

                    $value = $sheetXPath->evaluate(
                        'string(s:v)',
                        $cell
                    );

                    if ($cell->getAttribute('t') === 's') {
                        $value = $sharedStrings[(int) $value] ?? '';
                    }

                    if ($cell->getAttribute('t') === 'inlineStr') {
                        $value = '';

                        foreach (
                            $sheetXPath->query(
                                's:is//s:t',
                                $cell
                            ) as $text
                        ) {
                            $value .= $text->textContent;
                        }
                    }

                    if (
                        ! in_array(
                            $column,
                            ['A', 'B', 'C', 'D'],
                            true
                        )
                    ) {
                        if (trim($value) !== '') {
                            throw new \RuntimeException(
                                'Gunakan tepat empat kolom template.'
                            );
                        }

                        continue;
                    }

                    $values[ord($column) - 65] = trim($value);
                }

                if (implode('', $values) !== '') {
                    $rows[] = [
                        'line' => (int) $row->getAttribute('r'),
                        'values' => $values,
                    ];
                }
            }

            if (
                ! $rows
                || $rows[0]['values'] !== self::HEADERS
            ) {
                throw new \RuntimeException(
                    'Header harus: '.implode(', ', self::HEADERS)
                );
            }

            array_shift($rows);

            if (! $rows) {
                throw new \RuntimeException(
                    'Belum ada anggota pada file.'
                );
            }

            if (count($rows) > 1000) {
                throw new \RuntimeException(
                    'Maksimal 1000 anggota.'
                );
            }

            return $rows;
        } catch (\Throwable $exception) {
            throw ValidationException::withMessages([
                'workbook' => 'Excel tidak dapat diimpor: '
                    .$exception->getMessage(),
            ]);
        } finally {
            $zip->close();
        }
    }

    public function template(): string
    {
        $path = tempnam(
            sys_get_temp_dir(),
            'cms-xlsx-'
        );

        $zip = new ZipArchive;

        $zip->open($path, ZipArchive::OVERWRITE);

        $zip->addFromString(
            '[Content_Types].xml',
            '<?xml version="1.0"?>'
                .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
                .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
                .'<Default Extension="xml" ContentType="application/xml"/>'
                .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
                .'<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
                .'</Types>'
        );

        $zip->addFromString(
            '_rels/.rels',
            '<?xml version="1.0"?>'
                .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
                .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
                .'</Relationships>'
        );

        $zip->addFromString(
            'xl/workbook.xml',
            '<?xml version="1.0"?>'
                .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
                .'<sheets>'
                .'<sheet name="Anggota" sheetId="1" r:id="rId1"/>'
                .'</sheets>'
                .'</workbook>'
        );

        $zip->addFromString(
            'xl/_rels/workbook.xml.rels',
            '<?xml version="1.0"?>'
                .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
                .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
                .'</Relationships>'
        );

        $cells = '';

        foreach (self::HEADERS as $index => $header) {
            $column = chr(65 + $index);

            $cells .= '<c r="'.$column.'1" t="inlineStr">'
                .'<is><t>'.$header.'</t></is>'
                .'</c>';
        }

        $zip->addFromString(
            'xl/worksheets/sheet1.xml',
            '<?xml version="1.0"?>'
                .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
                .'<cols>'
                .'<col min="1" max="4" width="28" customWidth="1"/>'
                .'</cols>'
                .'<sheetData>'
                .'<row r="1">'.$cells.'</row>'
                .'</sheetData>'
                .'</worksheet>'
        );

        $zip->close();

        return $path;
    }
}