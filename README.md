# coachtechフリマ
## 環境構築
**Dockerビルド**
1. `git clone git@github.com:koto-101/mitani-coachtech-furima.git`
2. DockerDesktopアプリを立ち上げる
3. `docker-compose up -d --build`

**Laravel環境構築**
1. `docker-compose exec php bash`
2. `composer install`
3. 「.env.example」ファイルを コピーして「.env」を作成し、DBの設定を変更
# .env.example を .env ファイルとしてコピー
cp .env.example .env

# DBの設定を変更
``` text
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel_db
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_pass
```
5. アプリケーションキーの作成
``` bash
php artisan key:generate
```

6. マイグレーションの実行
``` bash
php artisan migrate
```

7. シーディングの実行
``` bash
php artisan db:seed
```

## 使用技術(実行環境)
OS: Windows 11
Laravel PHP 8.4.6 
PHP 8.2.27 
Fortify
formrequest
mailhog
stripe


## ER図
![ER図](index.png)

## URL
- 開発環境：http://localhost
- phpMyAdmin:：http://localhost:8080
- Mailhog（メール確認）: http://localhost:8025

---

### Stripe WebhookとCLIのセットアップ

Stripeの決済機能を実装するためには、Webhookを受け取る設定と、ローカルでStripeイベントをテストするためにStripe CLIを使用します。以下の手順でセットアップしてください。

#### 1. **Stripe CLIのインストール**

- macOS / Linux:
    ```bash
    curl -sS https://stripe.com/docs/stripe-cli#install | bash
    ```

- **Windows**:
    [Stripe CLI Windowsインストールガイド](https://stripe.com/docs/stripe-cli#install)を参照してください。

#### 2. Stripe CLIにログイン（初回のみ）
```bash
stripe login
```

#### 3. Laravelを起動
```bash
php artisan serve
```

#### 4.Webhookの中継設定
```bash
stripe listen --forward-to http://localhost:8000/stripe/webhook
```

#### 5..env に Webhook secret を記載
STRIPE_WEBHOOK_SECRET=whsec_*******

#### 6.補足：起動のたびに必要なコマンド（別ターミナルで実行）
``` bash
php artisan serve
stripe listen --forward-to http://localhost:8000/stripe/webhook
```

StripeのAPIキーやWebhook Secretは、Stripeダッシュボード(https://dashboard.stripe.com/test/apikeys) から取得してください

#### 7.Stripeの環境変数について
Stripeの決済機能を利用するには、以下の環境変数を.envまたは.env.testingに記述してください。
セキュリティの観点から、これらの値はGitに含めていません。
各開発者が 自分の Stripe アカウントで取得し、ローカルに設定する必要があります。

```env
STRIPE_KEY=your_stripe_publishable_key
STRIPE_SECRET=your_stripe_secret_key
STRIPE_WEBHOOK_SECRET=your_stripe_webhook_secret
```