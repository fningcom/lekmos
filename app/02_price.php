<?php
declare(strict_types=1);

$source = __DIR__ . DIRECTORY_SEPARATOR . 'db.dbf';
$date = date('Ymd');
$output = __DIR__ . DIRECTORY_SEPARATOR . "Сеть_Алтей.Аптека_Алтей.{$date}.txt";

if (!is_file($source)) {
    fwrite(STDERR, "Файл db.dbf не найден\n");
    exit(1);
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

fwrite($handle, "код\tцена\tколичество\n");

$count = dbase_numrecords($db);

for ($i = 1; $i <= $count; $i++) {
    $row = dbase_get_record_with_names($db, $i);
    if ($row === false || (!empty($row['deleted']) && $row['deleted'])) {
        continue;
    }

    $code = trim((string)($row['CODTMC'] ?? ''));
    $price = trim((string)($row['PRICE'] ?? ''));
    $quantity = trim((string)($row['OST'] ?? ''));

    fwrite($handle, $code . "\t" . $price . "\t" . $quantity . "\n");
}

fclose($handle);
dbase_close($db);

echo "Готово: {$output}\n";
