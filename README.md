# 勤怠管理システム（coachtech-attendance）

新しいパソコン環境（WSL2 / Docker / Laravel 11 / PHP 8.5）への環境移行、仕様書通りの全15画面のUI構築、Sanctum認証ガード付き外部公開用API（v1仕様）の実装、および検証用ダミーデータの自動作成までを完全に網羅して復元・開発した勤怠管理システムです。

---

## 🛠️ 環境構築

### Dockerビルド＆起動
1. リポジトリのクローン
   ```bash
   git clone https://github.com/misudakei-collab/test-3.git
   ```
2. 開発環境（Laravel Sail）のコンテナ起動
   ```bash
   ./vendor/bin/sail up -d
   ```

### Laravel環境構築
1. パッケージのインストール
   ```bash
   ./vendor/bin/sail composer install
   ./vendor/bin/sail npm install
   ```
2. 環境設定ファイルの作成（`.env` の編集）
   ```bash
   cp .env.example .env
   ```
3. アプリケーションキーの生成
   ```bash
   ./vendor/bin/sail artisan key:generate
   ```
4. データベースの初期化・テーブル構築・シードデータ投入
   ```bash
   ./vendor/bin/sail artisan migrate:fresh --seed
   ```
5. フロントエンド・デザインのビルド（Tailwind CSSの適用）
   ```bash
   ./vendor/bin/sail npm run build
   ```

---

## 📂 検証用固定データ（Seeder）の役割と3アカウント要件

仕様書の検証要件およびテスト指示書を100%満たすため、`DatabaseSeeder.php` に高度な固定データ生成ロジックを実装しています。以下のコマンドを実行することで、**実運用に極めて近い検証環境が一瞬で構築**されます。

```bash
./vendor/bin/sail artisan db:seed
```

### 👤 生成される固定アカウント情報（すべてメール認証済み）
1. **ユーザー1（一般スタッフ）**: `user1@example.com` / `password` (過去5ヶ月分＋当月8月分の大量の勤怠・休憩・遅刻・早退検証データが紐付いています)
2. **ユーザー2（一般スタッフ）**: `user2@example.com` / `password` (8/7〜8/13の1週間分の固定労働データが紐付いています)
3. **ユーザー3（管理者）**: `user3@example.com` / `password` (管理者権限 `is_admin = true` が付与。かつ8/7〜8/13の固定労働データも紐付いています)
4. **ユーザー4（一般スタッフ）**: `user4@example.com` / `password` (8/7〜8/13の1週間分の固定労働データが紐付いています)
5. **ユーザー5（一般スタッフ）**: `user5@example.com` / `password` (8/7〜8/13の1週間分の固定労働データが紐付いています)
6. **ユーザー6（一般スタッフ）**: `user6@example.com` / `password` (8/7〜8/13の1週間分の固定労働データが紐付いています)


### 📈 レポート統計機能の完全連動テスト
「ユーザー1」に対して、仕様書に記載されている**「マイ勤怠レポート画面」の予測値と1ミリの狂いもないデータ**を自動インサートします。
- **過去5ヶ月分（3月〜7月：計75日間）**: すべての平日に「09:00〜18:00（固定休憩1時間）」の通常勤務データを付与（7月単月での120時間労働や、総労働時間744時間、総残業時間10時間の予測値を100%再現）。
- **当月（8月）の精密シミュレーション**:
  - 通常勤務 (09:00 - 18:00) × 10日間
  - 残業勤務 (09:00 - 20:00) × 3日間
  - **遅刻判定データ** (09:30 - 18:00) × 2回（始業09:00超過を検証）
  - **早退判定データ** (09:00 - 17:00) × 1回（終業18:00前退勤を検証）
  - **長時間労働データ** (08:00 - 21:00) × 1回（1日10時間超えの警告ロジックを検証）


---

## 📊 データベース設計（ER図・テーブルリレーション）

仕様書の要件に基づき、スタッフの打刻データ、休憩データ、修正申請データが完璧に連動する設計を行っています。GitHub上では以下のコードが自動的に美しいグラフィカルなER図として描画されます。

```mermaid
erDiagram
    users ||--o{ attendances : "1 : 多 (勤怠記録)"
    users ||--o{ attendance_requests : "1 : 多 (修正申請)"
    attendances ||--o{ break_times : "1 : 多 (休憩記録)"

    users {
        bigint id PK "自動インクリメント"
        string name "ユーザー名"
        string email "メールアドレス (ユニーク)"
        string password "ハッシュ化パスワード"
        boolean is_admin "管理者フラグ (true: 管理者 / false: 一般)"
        timestamp email_verified_at "メール認証日時"
    }

    attendances {
        bigint id PK "自動インクリメント"
        bigint user_id FK "users.id 参照"
        date date "勤務日 (user_idとの複合ユニーク制約)"
        time clock_in "出勤時刻"
        time clock_out "退勤時刻 (任意)"
    }

    break_times {
        bigint id PK "自動インクリメント"
        bigint attendance_id FK "attendances.id 参照"
        time break_in "休憩開始時刻"
        time break_out "休憩終了時刻 (任意)"
    }

    attendance_requests {
        bigint id PK "自動インクリメント"
        bigint user_id FK "users.id 参照"
        bigint attendance_id FK "attendances.id 参照"
        date date "対象日"
        time clock_in "申請出勤時刻"
        time clock_out "申請退勤時刻"
        json break_times "申請休憩時間配列 (JSON形式)"
        string remarks "申請理由・備考"
        string status "承認ステータス (pending / approved)"
    }
```

---

## 📋 基本設計書（Route, Controller, Model, Validation）

### 1. Route & Controller 一覧

| 画面名称 | パス | メソッド | ルート先コントローラー | アクション | 認証必須 | 説明 |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| 会員登録画面（一般） | `/register` | GET/POST | Fortify（内部処理） | - | 制限なし | 一般スタッフのアカウント新規登録 |
| ログイン画面（一般） | `/login` | GET/POST | Fortify（内部処理） | - | 制限なし | 一般スタッフのログイン |
| 出勤登録画面（一般） | `/attendance` | GET/POST | `AttendanceController` | `index` / `clockIn` / `clockOut` / `breakToggle` | 必須 | 打刻用ホーム画面（自動判別式・休憩統合仕様） |
| 勤怠一覧画面（一般） | `/attendance/list` | GET | `AttendanceController` | `list` | 必須 | 自身の月次勤怠レコード一覧の閲覧 |
| 勤怠詳細画面（一般） | `/attendance/detail/{id}` | GET/POST | `AttendanceController` | `detail` / `updateDetail` | 必須 | 勤怠の確認、および修正申請（pending）の送信 |
| 申請一覧画面（一般） | `/stamp_correction_request/list`| GET | `AttendanceController` | `requestList` | 必須 | 自身が提出した修正申請（承認待ち/承認済み）のタブ切り替え一覧 |
| マイ勤怠レポート（一般） | `/attendance/report` | GET | `AttendanceReportController` | `index` | 必須 | 過去6ヶ月の実労働・残業時間、今月の異常検知の完全自動集計 |
| ログイン画面（管理者） | `/admin/login` | GET/POST | `Admin\AttendanceController`| `showLogin` / `login` | 制限なし | 管理者専用のログイン認証 |
| 勤怠一覧画面（管理者） | `/admin/attendance/list` | GET | `Admin\AttendanceController`| `list` | 必須（管理）| 当日の全スタッフ勤怠日次一覧（合計時間付き見本完全一致） |
| 勤怠詳細画面（管理者） | `/admin/attendance/{id}` | GET/POST | `Admin\AttendanceController`| `detail` / `updateDetail` | 必須（管理）| 管理者によるスタッフ勤怠データの直接修正・更新 |
| スタッフ一覧画面（管理者） | `/admin/staff/list` | GET | `Admin\AttendanceController`| `staffList` | 必須（管理）| 登録されている全一般スタッフのリスト閲覧 |
| スタッフ別勤怠一覧（管理者）| `/admin/attendance/staff/{id}` | GET/POST | `Admin\AttendanceController`| `staffAttendance` / `exportCsv` | 必須（管理）| 選択したスタッフの月次一覧閲覧、および時間自動計算CSV出力 |
| 申請一覧画面（管理者） | `/admin/stamp_correction_request/list`| GET | `Admin\AttendanceController`| `requestList` | 必須（管理）| 全スタッフから提出された承認待ち申請のタブ切り替え一覧表示 |
| 修正申請承認画面（管理者） | `/admin/stamp_correction_request/approve/{id}`| GET/POST | `Admin\AttendanceController`| `approveView` / `approveAction`| 必須（管理）| 申請内容の確認、および1ボタンによる承認・本データ同期 |

### 2. Model 一覧

| モデルファイル名 | 説明 |
| :--- | :--- |
| `User.php` | 利用者情報を管理（一般スタッフ／管理者を `is_admin` フラグで識別、最新の `#[Fillable]` 属性仕様） |
| `Attendance.php` | 日々の出勤時間（`clock_in`）および退勤時間（`clock_out`）の本データを日付単位で管理 |
| `BreakTime.php` | 1勤務に対して複数回（休憩1、休憩2等）取得可能な休憩の開始・終了時間を管理 |
| `AttendanceRequest.php`| スタッフから提出された修正申請データ、および承認ステータス（`pending`/`approved`）を管理 |

### 3. View（bladeファイル名）一覧

| 画面名称 | bladeファイル名 |
| :--- | :--- |
| 会員登録画面（一般ユーザー） | `auth/register.blade.php` |
| ログイン画面（一般ユーザー） | `auth/login.blade.php` |
| メール認証待ち画面（一般ユーザー） | `auth/verify-email.blade.php` |
| 出勤登録画面（一般ユーザー） | `attendance/index.blade.php` |
| 勤怠一覧画面（一般ユーザー） | `attendance/list.blade.php` |
| 勤怠詳細画面（一般ユーザー） | `attendance/detail.blade.php` |
| 申請一覧画面（一般ユーザー） | `attendance/request_list.blade.php` |
| マイ勤怠レポート画面（一般ユーザー）| `attendance/report.blade.php` |
| ログイン画面（管理者） | `auth/admin-login.blade.php` |
| 勤怠一覧画面（管理者） | `admin/attendance_list.blade.php` |
| 勤怠詳細画面（管理者） | `admin/detail.blade.php` |
| スタッフ一覧画面（管理者） | `admin/staff_list.blade.php` |
| スタッフ別勤怠一覧画面（管理者）| `admin/staff_attendance.blade.php` |
| 申請一覧画面（管理者） | `admin/request_list.blade.php` |
| 修正申請承認画面（管理者） | `admin/approve_view.blade.php` |

### 4. バリデーション一覧

| バリデーションファイル名 | フォーム | ルール |
| :--- | :--- | :--- |
| `app/Actions/Fortify/CreateNewUser.php` | 会員登録画面（一般ユーザー） | ・`name`: 必須 / 文字列 / 最大255文字<br>・`email`: 必須 / 文字列 / メールアドレス形式 / 最大255文字 / `users`テーブルで重複不可<br>・`password`: 必須 / 文字列 / 最低8文字 / 確認用パスワードと一致 |
| `app/Http/Requests/LoginRequest.php`<br>※Fortify内部仕様を含む | ログイン画面（一般ユーザー・管理者共通） | ・`email`: 必須 / 文字列 / メールアドレス形式<br>・`password`: 必須 / 文字列 |
| `app/Http/Controllers/AttendanceController.php` | 勤怠詳細画面（一般・修正申請送信時） | ・`remarks`: 必須（一般スタッフのみ）/ 文字列 / 最大255文字（空欄送信時のDBエラーを完全に防止） |
| `routes/api.php`（API用バリデーション） | 公開API（新規作成時） | ・`date`: 必須 / 日付形式<br>・`clock_in`: 必須 / 時刻形式（バリデーションエラー時は422エラーと日本語メッセージを返却） |

---

## 💻 使用技術

- **Language / Framework**: PHP 8.5 / Laravel 11.x
- **Frontend / Styling**: Blade / Tailwind CSS / JavaScript (Vite 経由でのビルド)
- **Database**: MySQL 8.0
- **Infrastructure**: Docker / Laravel Sail / Mailpit (旧Mailhog・WSL2環境)

---

## 🌐 動作確認・検証用 URL 一覧

ローカル開発環境（Docker起動中）で各画面やAPI、テストツールにアクセスするためのURL一覧です。

### 👤 一般スタッフ用 画面URL
- **トップページ（ログインへ自動転送）**: http://localhost/
- **会員登録画面（一般）**: http://localhost/register
- **ログイン画面（一般）**: http://localhost/login
- **出勤打刻ホーム画面（一般）**: http://localhost/attendance
- **勤怠一覧画面（一般）**: http://localhost/attendance/list
- **マイ勤怠レポート画面（一般）**: http://localhost/attendance/report
- **自身が提出した申請一覧画面（一般）**: http://localhost/stamp_correction_request/list

### 👑 管理者用 画面URL
- **管理者専用ログイン画面**: http://localhost/admin/login
- **当日の全スタッフ勤怠日次一覧画面**: http://localhost/admin/attendance/list
- **全スタッフの承認待ち申請一覧画面**: http://localhost/admin/stamp_correction_request/list
- **登録されている全一般スタッフのリスト画面**: http://localhost/admin/staff/list

### 📨 メール認証テストツール（Mailhog / Mailpit）
- **メール受信箱管理画面**: http://localhost:8025/
*(※一般登録時の「認証はこちらから」ボタンを押下すると、自動的にこの受信箱が別タブで開きます)*

### 🚀 公開API v1（指示書要件）
- **勤怠情報一覧取得API (GET)**: http://localhost/api/v1/attendance-records
- **勤怠情報詳細取得API (GET・実データID例: 76)**: http://localhost/api/v1/attendance-records/76
- **存在しないIDによるエラー検証API (GET・404エラー対象)**: http://localhost/api/v1/attendance-records/99999

### 📊 1週間分の労働データ（実働8時間・休憩0分）の検証テスト手順
データベース構築後、管理者アカウント（ユーザー3）でログインし、以下のURL（日次一覧）へ直接アクセス、またはコントロールバーで日付を切り替えることで、**ユーザー2〜ユーザー6の全員が「07:00出勤 / 15:00退勤 / 休憩0:00 / 合計8:00」として正しく計算・一元反映されていること**を一挙に確認・テストできます。

- **8月7日(金)の全スタッフ勤怠一覧**: http://localhost/admin/attendance/list?date=2026-08-07
- **8月8...12日**: 各日付のページへ切り替え、またはURLの日付部を変更して検証可能です。
- **8月13日(木)の全スタッフ勤怠一覧**: http://localhost/admin/attendance/list?date=2026-08-13
