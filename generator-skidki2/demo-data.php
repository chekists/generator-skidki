<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
if (!$USER->IsAdmin()) die("Вы не администратор!");

$countUser = 10; // количество пользователей
$discount = array(
    'min'=>1, //минимальная скидка
    'max'=>50 //максимальная скидка
); // количество пользователей


for ($i=0; $i<$countUser; $i++)
{
    $user = new CUser;
    $arFields = Array(
        "NAME"              => "Иван",
        "LAST_NAME"         => "Иванов",
        "EMAIL"             => "ivanov".$i."@microsoft.com",
        "LOGIN"             => "ivan_".$i,
        "LID"               => "ru",
        "ACTIVE"            => "Y",
        "GROUP_ID"          => array(3,6),
        "PASSWORD"          => "123456",
        "CONFIRM_PASSWORD"  => "123456",
    );
    $ID = $user->Add($arFields);
    if (intval($ID) > 0)
        echo "Пользователь #".($i+1)." успешно добавлен.<br>";
    else
        echo $user->LAST_ERROR;
}
for ($i=$discount['min']; $i<=$discount['max']; $i++) {
    $arFields = array(
        "LID" => 's1',
        "NAME" => $i . "% Скидки " . date("d.m.y"),
        'PRIORITY' => 1,
        'SORT' => 100,
        "CURRENCY" => "RUB",
        "ACTIVE" => "Y",
        "USER_GROUPS" => array(2),
        "ACTIVE_FROM" => null,
        "ACTIVE_TO" => null,
        "XML_ID"=>'generation_'.$i,
        'LAST_DISCOUNT' => 'Y',
        'ACTIONS' => [
            'CLASS_ID' => 'CondGroup',
            'DATA' => [
                'All' => 'AND',
            ],
            'CHILDREN' => [
                [
                    'CLASS_ID' => 'ActSaleBsktGrp',
                    'DATA' => [
                        'Type' => 'Discount',
                        'Value' => $i,
                        'Unit' => "Perc",
                        'All' => 'AND',
                        'Max' => '0',
                        'True' => 'True'
                    ],
                    'CHILDREN' => []
                ]
            ]
        ],
        'CONDITIONS' => [
            'CLASS_ID' => 'CondGroup',
            'DATA' => [
                'All' => 'AND',
                'True' => 'True',
            ],
            'CHILDREN' => []
        ]
    );
    $ID = CSaleDiscount::Add($arFields); //Создаем правило корзины
    echo "Правило корзины ".$i."% успешно добавлено.<br>";
}
