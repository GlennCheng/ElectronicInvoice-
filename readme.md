# 台灣電子發票載具歸戶 

此套件用於將指定的會員attribute（此attribute是存放發票的廠商自訂載具，且每個會員需是唯一值）
歸戶至手機條碼或是自然人憑證 。

詳細電子發票載具細節請參考
[電子發票說明] 

[電子發票說明]:
    http://cloud.google.com/natural-language

## Setup

### 後台設定

於後台AstralWeb->Electronic Invoice中設定否開啟測試或正式

以及廠商統編, attrbute_code等等


### 前端設定
因此套件登入會員後並直接進入controller就可跑完流程，

所以只需於後台設定完成後將YOUR_BASE_URL/electronic-invoice/account/einvoice此連結放於自訂位置即可

