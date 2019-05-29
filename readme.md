# 台灣電子發票載具歸戶 

此套件用於將指定的會員attribute（此attribute是存放發票的廠商自訂載具，且每個會員需是唯一值）
歸戶至手機條碼或是自然人憑證 。

詳細電子發票載具細節請參考
[電子發票說明] 

[電子發票說明]:
    https://github.com/AstralWebTW/ElectronicInvoice/blob/master/%E9%9B%BB%E5%AD%90%E7%99%BC%E7%A5%A8%E8%AA%AA%E6%98%8E.pdf

## Setup

### 後台設定

於後台AstralWeb->Electronic Invoice中設定否開啟測試或正式

以及廠商統編, attrbute_code等等


### 前端設定
因此套件登入會員後並直接進入controller就可跑完流程，

所以只需於後台設定完成後將YOUR_BASE_URL/electronic-invoice/account/einvoice此連結放於自訂位置即可


## 安裝方式 1
### 使用 Composer（ 推薦 ）：
於 ```/composer.json``` 檔案內新增以下欄位
> 避免正式上線後系統產生錯誤，建議版本給予至第三位小數點

```json
"require": {
        //...
        "astralweb/post-management": "1.0.0"
    },
"repositories": [
          //...
          {
              "type": "vcs",
              "url":  "git@github.com:AstralWebTW/post-management.git"
          }
      ]
```

接著於 ```Command line``` 中輸入以下指令：

```sh
composer update
```
>若是權限不足的話，可以給予 ```sudo```權限

因為本套件放在私有 ```Repo``` 內，Github 會要求需要使用 Token

等待 Composer 計算完套件相依性，並把套件下載後，接著執行

```sh
 bin/magneto setup:upgrade
```
完成安裝

<br><br>

## 安裝方式 2
### 直接下載 Repo：

把 Repo 下載後，複製至專案資料夾 ```/app/code/astralweb/post```  內，接著執行指令即可完成安裝(若沒有資料夾請自行建立)。

```sh
bin/magneto setup:upgrade
```
<br><br>
