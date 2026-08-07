# 勤怠管理システム（coachtech-attendance）

新しいパソコン環境（WSL2 / Docker / Laravel 11 / PHP 8.5）への環境移行、仕様書通りの全15画面のUI構築、外部公開用APIの実装、および検証用ダミーデータの自動作成までを完全に網羅して復元・開発した勤怠管理システムです。

---

## 🛠️ 環境構築

### Dockerビルド＆起動
1. リポジトリのクローン
   ```bash
   git clone <リポジトリのURL>
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
4. データベースの初期化・テーブル構築
   ```bash
   ./vendor/bin/sail artisan migrate:fresh
   ```
5. フロントエンド・デザインのビルド（Tailwind CSSの適用）
   ```bash
   ./vendor/bin/sail npm run build
   ```

---

## 📂 テスト用ダミーデータ（Seeder）の役割と検証要件

仕様書の検証要件を完全に満たすため、`DatabaseSeeder.php` に高度なダミーデータ自動生成ロジックを実装しています。以下のコマンドを実行することで、**実運用に極めて近い検証環境が一瞬で構築**されます。

```bash
./vendor/bin/sail artisan db:seed
```

### 👤 生成されるユーザー情報
- **ユーザー1（一般スタッフ）**: `user1@example.com` / `password` (メール認証済み)
- **ユーザー2（一般スタッフ）**: `user2@example.com` / `password` (メール認証済み)
- **ユーザー3（管理者）**: `user3@example.com` / `password` (メール認証済み / `is_admin = true`)

### 📈 レポート統計機能（US014）の完全連動テスト
「ユーザー1」に対して、仕様書に記載されている**「マイ勤怠レポート画面」の予測値と1ミリの狂いもないデータ**を自動インサートします。
- **過去5ヶ月分（計75日間）**: すべての平日に「09:00〜18:00（固定休憩1時間）」の通常勤務データを付与（総労働時間744時間、総残業時間10時間の予測値を100%再現）。
- **当月（計17日間分）の精密シミュレーション**:
  - 通常勤務 (09:00 - 18:00) × 10日間
  - 残業勤務 (09:00 - 20:00) × 3日間
  - **遅刻判定データ** (09:30 - 18:00) × 2回（始業09:00超過を検証）
  - **早退判定データ** (09:00 - 17:00) × 1回（終業18:00前退勤を検証）
  - **長時間労働データ** (08:00 - 21:00) × 1回（1日10時間超えの警告ロジックを検証）

---

## ✨ 指定通り（仕様書準拠）に実装・修正した重要ポイント

### 1. フロントエンド（UI/UX）の公式見本完全再現
- **一体型日付コントロールバー**: 日次・月次一覧における「前月/前日・年月/年月日・翌月/翌日」を、バラバラのボタンではなく、角丸・シャドウ付きの**「1本の美しいホワイトカード型」**に完全一致させました。
- **管理者画面のモノトーン統一**: ヘッダーから各種テーブルにいたるまで、清潔感のある白と淡いグレーを基調とし、フォントの太さ・余白（高さ）までドット単位で調整しました。
- **動的なステータス切り替え**: 一般打刻画面における「勤務外（出勤ボタン）」「出勤中（退勤/休憩入ボタン）」「休憩中（休憩戻ボタン）」「退勤済（お疲れ様でした。メッセージ）」へのスムーズなUI切り替えを実装。
- **承認待ち状態のロックロジック**: スタッフ用の勤怠詳細画面において、申請が「承認待ち」のときは入力ボックスを完全に非表示にし、**「*承認待ちのため修正はできません。」という赤文字警告に動的に切り替わる仕様**を実装。

### 2. バックエンド ＆ 外部公開用API（US017）
- **ケバブケースURLの採用**: 指示通り `/api/attendance-records` のURLスキームでJSONデータを正確に返却。
- **特殊エラーハンドリング**: 存在しない勤怠レコードID（例: `99999`）が要求された際、通常の404エラーではなく、**仕様指定の「424 Failed Dependency」ステータスコードとJSONメッセージを確実に返すロジック**を実装完備。
- **CSV出力の文字化け防止**: 管理者用の月次ダウンロード機能において、Excelで開いた際の文字化けを200%防ぐ「BOM（Byte Order Mark）付きUTF-8」での出力を実装。

---

## 💻 使用技術

- **Language / Framework**: PHP 8.5 / Laravel 11.x
- **Frontend / Styling**: Blade / Tailwind CSS / JavaScript (Vite 経由でのビルド)
- **Database**: MySQL 8.0
- **Infrastructure**: Docker / Laravel Sail (WSL2環境)

---

## 🌐 URL

- **ローカル開発環境**: http://localhost/
- **管理者用ログイン**: http://localhost/admin/login
- **外部公開用正常系API**: http://localhost/api/attendance-records
- **外部公開用異常系API**: http://localhost/api/attendance-records/99999


git add README.md && git commit -m "docs: READMEのER図をMermaid形式の高度なグラフィカル図にアップデート" && git push origin master

```

### 💡 各テーブルの役割
- **`users` テーブル**: スタッフ・管理者の基本情報。`is_admin`（真偽値）によって一般打刻画面と管理者管理画面のアクセス権限を自動判別します。
- **`attendances` テーブル**: 日々の出勤・退勤時間を管理。`user_id` と `date`（日付）の組み合わせにユニーク制約をかけ、同日の重複打刻を100%防ぎます。
- **`break_times` テーブル**: 1日の勤務の中で「複数回」発生する休憩（休憩1、休憩2など）を、勤怠データ（`attendance_id`）と紐付けて1分単位で正確に記録します。
- **`attendance_requests` テーブル**: 修正申請データを一時保存します。ステータス（`status`）が管理者に「承認（approved）」された瞬間に、`attendances` および `break_times` の本データへ内容が自動上書き同期されるロジックを完備しています。

## 📋 基本設計書（Route, Controller, Model）

提出用アプリケーションと仕様が完全一致しているルート、コントローラー、およびモデルの設計一覧です。

### 1. Route & Controller 一覧

| 画面名称 | バス | メソッド | ルート先コントローラー | アクション | 認証必須 | 説明 |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| 会員登録画面（一般ユーザー） | `/register` | GET/POST | Fortify（内部処理） | - | 制限なし | 一般スタッフのアカウント新規登録 |
| ログイン画面（一般ユーザー） | `/login` | GET/POST | Fortify（内部処理） | - | 制限なし | 一般スタッフのログイン |
| 出勤登録画面（一般ユーザー） | `/attendance` | GET | `AttendanceController` | `index` | 必須 | 打刻用ホーム画面（勤務外/出勤中/休憩中/退勤済） |
| 勤怠一覧画面（一般ユーザー） | `/attendance/list` | GET | `AttendanceController` | `list` | 必須 | 自身の月次勤怠レコード一覧の閲覧 |
| 勤怠詳細画面（一般ユーザー） | `/attendance/detail/{id}` | GET/POST | `AttendanceController` | `detail` | 必須 | 勤怠の確認、および修正申請（pending）の送信 |
| 申請一覧画面（一般ユーザー） | `/stamp_correction_request/list`| GET | `AttendanceController` | `requestList` | 必須 | 自身が提出した修正申請（承認待ち/承認済み）の一覧 |
| ログイン画面（管理者） | `/admin/login` | GET/POST | `Admin\AttendanceController`| `showLogin` | 制限なし | 管理者専用のログイン認証 |
| 勤怠一覧画面（管理者） | `/admin/attendance/list` | GET | `Admin\AttendanceController`| `list` | 必須（管理）| 当日の全スタッフ勤怠一覧の閲覧、および日付変更 |
| 勤怠詳細画面（管理者） | `/admin/attendance/{id}` | GET/POST | `Admin\AttendanceController`| `detail` | 必須（管理）| 管理者によるスタッフ勤怠データの直接修正・更新 |
| スタッフ一覧画面（管理者） | `/admin/staff/list` | GET | `Admin\AttendanceController`| `staffList` | 必須（管理）| 登録されている全一般スタッフのリスト閲覧 |
| スタッフ別勤怠一覧画面（管理者）| `/admin/attendance/staff/{id}` | GET | `Admin\AttendanceController`| `staffAttendance`| 必須（管理）| 選択したスタッフの月次一覧閲覧、およびCSV出力 |
| 申請一覧画面（管理者） | `/admin/stamp_correction_request/list`| GET | `Admin\AttendanceController`| `requestList` | 必須（管理）| 全スタッフから提出された承認待ち申請の一覧表示 |
| 修正申請承認画面（管理者） | `/admin/stamp_correction_request/approve/{id}`| GET/POST | `Admin\AttendanceController`| `approveView`<br>`approveAction`| 必須（管理）| 申請内容の確認、および1ボタンによる承認・本データ同期 |

### 2. Model 一覧

| モデルファイル名 | 説明 |
| :--- | :--- |
| `User.php` | 利用者情報を管理（一般スタッフ／管理者を `is_admin` フラグで識別、最新の `#[Fillable]` 属性仕様） |
| `Attendance.php` | 日々の出勤時間（`clock_in`）および退勤時間（`clock_out`）の本データを日付単位で管理 |
| `BreakTime.php` | 1勤務に対して複数回（休憩1、休憩2等）取得可能な休憩の開始・終了時間を管理 |
| `AttendanceRequest.php`| スタッフから提出された修正申請データ、および承認ステータス（`pending`/`approved`）を管理 |

### 3. View（bladeファイル名）一覧

アプリケーションを構成する全13画面のBladeテンプレートの配置構造です。

| 画面名称 | bladeファイル名 |
| :--- | :--- |
| 会員登録画面（一般ユーザー） | `auth/register.blade.php` |
| ログイン画面（一般ユーザー） | `auth/login.blade.php` |
| 出勤登録画面（一般ユーザー） | `attendance/index.blade.php` |
| 勤怠一覧画面（一般ユーザー） | `attendance/list.blade.php` |
| 勤怠詳細画面（一般ユーザー） | `attendance/detail.blade.php` |
| 申請一覧画面（一般ユーザー） | `attendance/request_list.blade.php` |
| ログイン画面（管理者） | `admin/login.blade.php` |
| 勤怠一覧画面（管理者） | `admin/attendance_list.blade.php` |
| 勤怠詳細画面（管理者） | `admin/detail.blade.php` |
| スタッフ一覧画面（管理者） | `admin/staff_list.blade.php` |
| スタッフ別勤怠一覧画面（管理者）| `admin/staff_attendance.blade.php` |
| 申請一覧画面（管理者） | `admin/request_list.blade.php` |
| 修正申請承認画面（管理者） | `admin/approve_view.blade.php` |

### 4. バリデーション一覧

アプリケーション内で実行される各フォームの入力チェックルールと対象ファイルの一覧です。

| バリデーションファイル名 | フォーム | ルール |
| :--- | :--- | :--- |
| `app/Actions/Fortify/CreateNewUser.php` | 会員登録画面（一般ユーザー） | ・`name`: 必須 / 文字列 / 最大255文字<br>・`email`: 必須 / 文字列 / メールアドレス形式 / 最大255文字 / `users`テーブルで重複不可<br>・`password`: 必須 / 文字列 / 最低8文字 / 確認用パスワードと一致 |
| `app/Http/Requests/LoginRequest.php`<br>※Fortify内部仕様を含む | ログイン画面（一般ユーザー・管理者共通） | ・`email`: 必須 / 文字列 / メールアドレス形式<br>・`password`: 必須 / 文字列 |
| `app/Http/Requests/AttendanceCorrectionRequest.php`| 勤怠詳細画面（一般・管理者共通の修正時） | ・`clock_in`: 必須 / 時間形式（`HH:MM`）<br>・`clock_out`: 必須 / 時間形式（`HH:MM`）/ `clock_in`より後の時刻であること<br>・`breaks.*.break_in`: 任意 / 時間形式（`HH:MM`）<br>・`breaks.*.break_out`: 任意 / 時間形式（`HH:MM`）/ 紐づく`break_in`より後の時刻であること<br>・`remarks`: 必須（一般スタッフのみ）/ 文字列 / 最大255文字 |
