<?
define(NEED_AUTH,true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Генератор скидки");
CJSCore::Init(array("jquery"));
?>

    <a id="get" href="#" class="ya-btn" onclick="getDiscount(this);return false;">Получить скидку</a>
    <div class="message" id="message_code"></div>
    <div class="error" id="error_code"></div>
    <form class="ya-form" method="post" onsubmit="chekDiscount($(this).find('[name=code]').val());return false;">
        <input type="text" name="code" placeholder="Код скидки">
        <input class="ya-btn" name="btn" type="submit" value="Проверить скидку">
    </form>
    <div class="message" id="message"></div>
    <div class="error" id="error"></div>

    <script>
    function getDiscount(btn)
    {
        $('#message_code').html('');
        $('#error_code').html('');
        $.ajax({
            type: "post",
            url: '/generator-skidki/discount.php',
            data: {
                action: 'get',
            },
            dataType: "json",
            success: function(out){
                console.log(out);
                if(!out.error && out.code)
                {
                    $('#message_code').html('Ваш код скидки: '+out.code+'<br> Ваша скидка: '+out.percent+'%');
                }
                else if(out.error)
                {
                    $('#error_code').html('Ошибка: '+out.error);
                }
            },
            error:
                function(jqXHR, textStatus, errorThrown){
                    console.log(jqXHR.responseText);
                    console.log(textStatus);
                    console.log(errorThrown);
                }
        });
    }
    function chekDiscount(code)
    {
        $('#message').html('');
        $('#error').html('');
        $.ajax({
            type: "post",
            url: '/generator-skidki/discount.php',
            data: {
                action: 'check',
                code: code
            },
            dataType: "json",
            success: function(out){
                if(!out.error && out.code)
                {
                    $('#message').html('Ваша скидка: '+out.percent+'%');
                }
                else if(out.error)
                {
                    $('#error').html('Ошибка: '+out.error);
                }
            },
            error:
                function(jqXHR, textStatus, errorThrown){
                    console.log(jqXHR.responseText);
                    console.log(textStatus);
                    console.log(errorThrown);
                }
        });
    }

    </script>





    <style>
        .ya-btn
        {
            padding: 10px 12px;
            border: none;
            background: #f3464ff2;
            margin: 50px 100px 50px 0;
            color: #fff;
            text-decoration: unset;
            display: inline-block;
        }
        .ya-btn:hover,
        .ya-form>.ya-btn:hover
        {
            background: rgba(55, 42, 233, 0.95);
        }
        .ya-form
        {
            width:700px;
            display: flex;
            align-items: flex-end;
        }
        .ya-form>*
        {
            height: 50px;
            margin: 0 20px 0 0;
        }
        .ya-form>.btn
        {
            background: rgba(0, 183, 70, 0.95);
        }
        [name="code"]
        {
            width: calc(100% - 177px);
        }
        .error
        {
            padding: 30px 0;
            color: #c71239;
            display: inline-block;

        }
        .message
        {
            padding: 30px 0;
            color: #0b9c0f;
            display: inline-block;
        }
    </style>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>