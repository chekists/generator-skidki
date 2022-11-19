<?
define(NEED_AUTH,true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Генератор скидки");
?>

    <button>Получить скидку</button>
    <style>
        button
        {
            padding: 10px 12px;
            border: none;
            background: #f3464ff2;
            margin: 50px 100px;
            color: #fff;
        }
        button:hover
        {
            background: #281dadf2;
        }
    </style>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>