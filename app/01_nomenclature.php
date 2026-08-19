<?php
declare(strict_types=1);

$source = __DIR__ . DIRECTORY_SEPARATOR . 'db.dbf';
$date = date('Ymd');
$output = __DIR__ . DIRECTORY_SEPARATOR . "Сеть_Алтей.номенклатура.{$date}.csv";

if (!is_file($source)) {
    fwrite(STDERR, "Файл db.dbf не найден\n");
    exit(1);
}

function normalizeText(?string $value, string $default = ''): string
{
    $value = $value ?? '';
    $value = str_replace(['\\', "\t", "\r", "\n", "\f", "\v"], ' ', $value);
    $value = preg_replace('/[\x00-\x1F\x7F]+/u', ' ', $value) ?? '';
    $value = preg_replace('/\s+/u', ' ', $value) ?? '';
    $value = trim($value);

    if ($value !== '') {
        $value = mb_strtolower(mb_substr($value, 0, 1, 'UTF-8'), 'UTF-8')
               . mb_substr($value, 1, null, 'UTF-8');
    }

    return $value !== '' ? $value : $default;
}

$db = dbase_open($source, 0);
if ($db === false) {
    fwrite(STDERR, "Не удалось открыть DBF\n");
    exit(1);
}

$handle = fopen($output, 'wb');
if ($handle === false) {
    dbase_close($db);
    fwrite(STDERR, "Не удалось создать файл результата\n");
    exit(1);
}

fwrite($handle, "\xEF\xBB\xBF");
fwrite($handle, "наименование\tпроизводитель\tкод\n");

$count = dbase_numrecords($db);

for ($i = 1; $i <= $count; $i++) {
    $row = dbase_get_record_with_names($db, $i);
    if ($row === false || (!empty($row['deleted']) && $row['deleted'])) {
        continue;
    }

    $name = normalizeText((string)($row['NAME'] ?? ''));
    $maker = normalizeText((string)($row['MAKER'] ?? ''), 'Не определен');
    $code = trim((string)($row['CODTMC'] ?? ''));

    fwrite($handle, $name . "\t" . $maker . "\t" . $code . "\n");
}

fclose($handle);
dbase_close($db);

echo "Готово: {$output}\n";
