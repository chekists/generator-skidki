<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
use Bitrix\Sale\Internals;
CModule::IncludeModule("catalog");
CModule::IncludeModule("sale");
global $APPLICATION, $USER;
if(!$USER->IsAuthorized())
{
    die(json_encode(['error'=>'Вы не авторизованы']));
}
$userId = $USER->GetID();
$request = \Bitrix\Main\Context::getCurrent()->getRequest();
$action = strip_tags(trim($request->get("action")));
$discountStart = strtotime(date("d.m.Y H:i:s"));
switch ($action) {
    case 'get':
        $arResultDiscount = array();
        $filter = array('USER_ID'=>$userId, 'ACTIVE'=>'Y', '>=ACTIVE_TO'=> new \Bitrix\Main\Type\DateTime(
            ConvertTimeStamp($discountStart, "FULL")
        ));
        $coupon = getCoupon($filter);
        if(!empty($coupon['COUPON']))
        {
            echo json_encode(['code'=>$coupon['COUPON'], 'percent'=>$coupon['PERCENT']]);
            break;
        }
        $discountEnd = $discountStart + 3 * 60 * 60; //3 часа
        $ID = getIdRandomRule();
        $res = $ID;
        if ($res) {
            $codeCoupon = CatalogGenerateCoupon(); //Генирация кода купона
            $fields["DISCOUNT_ID"] = $ID;
            $fields["COUPON"] = $codeCoupon;
            $fields["ACTIVE"] = "Y";
            $fields["TYPE"] = 2;
            $fields["MAX_USE"] = 1;
            $fields["USER_ID"] = $userId;
            $start = new \Bitrix\Main\Type\DateTime(
                ConvertTimeStamp($discountStart, "FULL")
            );
            $end = new \Bitrix\Main\Type\DateTime(
                ConvertTimeStamp($discountEnd, "FULL")
            );
            $fields["ACTIVE_FROM"] = $start;
            $fields["ACTIVE_TO"] = $end;
            $dd = Internals\DiscountCouponTable::add(
                $fields
            ); //Создаем купон для этого правила
            if (!$dd->isSuccess()) {
                $err = $dd->getErrorMessages();
            } else {
                $arResultDiscount['code'] = $codeCoupon;
                $arResultDiscount['percent'] = getRulDiscountPercent($res);
            }
        } else {
            $ex = $APPLICATION->GetException();
            $arResultDiscount['error'] = $ex->GetString();
        }
        echo json_encode($arResultDiscount);
        break;
    case 'check':
        $code = strip_tags(trim($request->get("code")));
        if(!empty($code))
        {

            $filter = array('COUPON'=>$code,'USER_ID'=>$userId, 'ACTIVE'=>'Y', '>=ACTIVE_TO'=> new \Bitrix\Main\Type\DateTime(
            ConvertTimeStamp($discountStart, "FULL")
            ));
            $coupon = getCoupon($filter);
            if(!empty($coupon['COUPON']))
                echo json_encode(['code'=>$code,'percent'=>$coupon['PERCENT']]);
            else
            {
                echo json_encode(['error'=>'Скидка недоступна']);
            }
        }
        else
        {
            echo json_encode(['error'=>'поле "Код скидки" не заполнено']);
        }
        break;
}
function getIdRandomRule()
{
    $ar = [];
    $db_res = CSaleDiscount::GetList(
        array(),
        array(
            "LID" => SITE_ID,
            "ACTIVE" => "Y",
            "%XML_ID" => 'generation_',
        ),
        false,
        false,
        array('ID')
    );
    while ($ar_res = $db_res->Fetch())
    {
        $ar[] = $ar_res;
    }
    $key = rand(0, count($ar)-1);
    return $ar[$key]['ID'];
}
function getCoupon($filter)
{
    $couponData = array('COUPON'=>'');
    $couponIterator = Internals\DiscountCouponTable::getList(array(
        'select' => array('ID','ACTIVE_FROM','ACTIVE_TO','COUPON','USER_ID','DISCOUNT_ID',),
        'filter' => $filter
    ));
    if ($coupon = $couponIterator->fetch())
    {
        $couponData = $coupon;
        //$rule = CSaleDiscount::GetByID($couponData['DISCOUNT_ID']);
        //preg_match("/'VALUE' => -([.0-9]*)/m", $rule['APPLICATION'], $matches);
        //$couponData['PERCENT'] = $matches[1];
        $couponData['PERCENT'] = getRulDiscountPercent($couponData['DISCOUNT_ID']);
    }
    return $couponData;
}
function getRulDiscountPercent($ruleId)
{
    global $DB;
    $results = $DB->Query("SELECT SHORT_DESCRIPTION,ACTIONS FROM b_sale_discount WHERE ID=$ruleId");
    if ($row = $results->Fetch())
    {
        //$data = unserialize($row['SHORT_DESCRIPTION']);
        //return $data['VALUE'];

        $data = unserialize($row['ACTIONS']);
        return $data['CHILDREN'][0]['DATA']['Value'];
    }
}