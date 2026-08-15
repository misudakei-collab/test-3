 勤怠管理システム（coachtech-attendance）

本アプリケーションは、一般スタッフの日々の正確な打刻、勤務実績の可視化、および管理者による勤怠データのガバナンス（一元管理・直接修正・承認フロー）を円滑に行うために開発された、フル機能の**「クラウド型 勤怠管理システム」**です。

新環境（WSL2 / Docker / Laravel 11 / PHP 8.5）への完全な移行、仕様書通りの全15画面のUI再現、およびSanctum認証ガード付き外部公開用API（v1仕様）までを網羅して実装・復元しています。

---

## 🌟 アカウント権限とシステム宣言

本システムは、ログインするユーザーの属性（`is_admin` フラグ）に応じて**「一般スタッフ環境」**と**「管理者環境」**の2つの独立したコックピット（機能・画面）が動的に切り替わる完全制限ガード仕様となっています。

### 👥 1. 一般スタッフ用アカウント（スタッフ専用環境）
一般スタッフとしてシステムにログインすると、自身の勤務に特化した直感的で洗練された以下の機能が利用できます。
- **出勤打刻ホーム画面（PG03）**: ボタン一発で「出勤・退勤・休憩開始・休憩終了」を記録（現在の状態に応じた自動ボタン制御）。
- **勤怠一覧画面（PG04）**: 自身の当月の勤務・休憩・合計（実働）時間を日本語曜日付きのカレンダー形式で確認。
- **勤怠詳細・修正申請（PG05）**: 過去の打刻ミスを発見した際、管理者に「修正理由」を添えて承認待ち（pending）申請を送信。
- **申請一覧画面（PG06）**: 自身が提出した申請の進捗状況を「承認待ち」「承認済み」のタブ切り替えで一元管理。
- **マイ勤怠レポート（PG14）**: 過去6ヶ月の「総労働時間」「総残業時間」「平均労働時間」の統計、および当月の「遅刻・早退・長時間労働」を自動検知するダッシュボード。

### 👑 2. 管理者用アカウント（最高管理者環境）
管理者としてシステムにログインすると、URLの先頭に `/admin` が強制付与され、全スタッフの勤務統制を行うための強力な以下の管理機能が開放されます。
- **勤怠一覧画面（管理者用・PG08）**: 「本日」出勤している全スタッフの勤務状況（出退勤・休憩・実働合計時間）を横並びでリアルタイムに監視。
- **勤怠データの直接修正（PG09）**: スタッフの打刻漏れなどに対し、管理者が直接データを書き換えて即時反映。
- **スタッフ一覧・月次勤怠閲覧（PG10/PG11）**: 登録されている全スタッフの名簿、および選択した特定のスタッフの月次出勤簿を閲覧。
- **文字化け防止CSV出力（FN045）**: スタッフ別の月次勤怠データを、Excelで開いても絶対に文字化けしない「BOM付きUTF-8」形式のCSVとして1秒でエクスポート。
- **修正申請の承認・却下（PG12/PG13）**: 一般スタッフから上がってきた修正申請を審査し、ボタン1つで本データへ同期・承認適用。

---


## 🛠️ 環境構築手順

### 1. 【前提条件】必要なソフトウェアの準備
開発環境を動かすために、お使いのOSに合わせて以下の基本ツールを事前にインストールしてください。

- **Windows環境の場合**:
  1. **WSL2 (Windows Subsystem for Linux)**: ターミナル（PowerShell等）を管理者権限で開き、`wsl --install` を実行してUbuntu等のLinux環境を構築。
  2. **Docker Desktop**: 公式サイトからWindows用をダウンロード。設定の「Use the WSL 2 based engine」に必ずチェックを入れ、Linux連携を有効化。
  3. **VS Code（Visual Studio Code）**: コードエディタをインストール。拡張機能「WSL」を入れてLinux上のディレクトリを直接開けるように設定。
- **Mac環境の場合**:
  1. **Docker Desktop for Mac**: 公式サイトからMac用（Apple Silicon製/Intel製を選択）をインストールして起動。
  2. **VS Code**: 公式サイトよりダウンロードしてインストール。

---

### 2. リポジトリの取得とWSL2上での展開
Linux環境（WSL2内のUbuntu等）またはMacのターミナルを立ち上げ、以下のコマンドを実行します。

```bash
# 適切な作業ディレクトリ（例: ~/src や ~/projects）へ移動
cd coachtech-attendance

# 1. リポジトリのクローン
git clone https://github.com/misudakei-collab/test-3.git

# 2. クローンしたプロジェクトフォルダへ移動
cd test-3

# 3. VS Codeでプロジェクトを開く（Windowsの場合はWSL環境内として展開されます）
code .
```

---

### 3. Laravel Sail（Docker環境）のビルド・起動
本システムは `Laravel Sail` を用いて、PHP・MySQL・Mailpitなどのコンテナ群を1つのコマンドで仮想化しています。VS Code内のターミナルを開き、以下を実行してください。

```bash
# 1. 初回のコンテナビルドおよびバックグラウンド起動（環境によって3〜10分ほどかかります）
./vendor/bin/sail up -d

# ※正常に起動すると、PHP(Laravel)、MySQL、Mailpit(Mailhog)の各コンテナが稼働状態になります。
```

---

### 4. アプリケーションの初期設定とライブラリ適用
起動した仮想コンテナの内部へ、システムを動かすための必要な依存パッケージと設定ファイルを流し込みします。

```bash
# 1. バックエンド用パッケージ（PHPライブラリ）の強制同期インストール
./vendor/bin/sail composer install

# 2. フロントエンド用パッケージ（JavaScript / CSSツール）のインストール
./vendor/bin/sail npm install

# 3. 環境設定ファイル（.env）の生成
cp .env.example .env

# 4. 暗号化通信用アプリケーションキーの自動生成（.envへ書き込まれます）
./vendor/bin/sail artisan key:generate
```

---

### 5. テーブル構造の構築 ＆ シードデータの一括インサート
データベース内にテーブルを全自動構築し、指示書に準拠したテストアカウント群およびダミー実績データを一挙に自動生成します。

```bash
# テーブル構築 ＆ シードデータ（6名分の検証用実績データ）の一括インサート
./vendor/bin/sail artisan migrate:fresh --seed
```

---

### 6. アセットのビルド（フロントエンドの起動）
Tailwind CSSなどのデザイン設定を最適化し、実際のWebブラウザに美しいUIを表示させるためのアセットコンパイルを実行します。

```bash
# デザイン・フロントエンド資材を本番用にビルド
./vendor/bin/sail npm run build
```

---

👉 **環境構築完了！**
ここまでの手順がすべて完了すると、ブラウザから [http://localhost/](http://localhost/) へアクセスすることで、本システムが何不自由なくサクサクと完璧に動作します。


## 📂 検証用固定データ（Seeder）の構成

仕様書の検証要件およびテスト指示書を100%満たすため、`DatabaseSeeder.php` に高度な固定データ生成ロジックを実装しています。

### 👤 生成される固定アカウント情報（すべてメール認証済み）
1. **ユーザー1（一般スタッフ）**: `user1@example.com` / `password` (過去5ヶ月分＋当月7月分の精密な統計検証データが紐付いています)
2. **ユーザー2（一般スタッフ）**: `user2@example.com` / `password` (7/7〜7/13の1週間分の固定労働データが紐付いています)
3. **ユーザー3（最高管理者）**: `user3@example.com` / `password` (管理者権限 `is_admin = true` が付与。かつ7/7〜7/13の固定労働データも紐付いています)
4. **ユーザー4（一般スタッフ）**: `user4@example.com` / `password` (7/7〜7/13の1週間分の固定労働データが紐付いています)
5. **ユーザー5（一般スタッフ）**: `user5@example.com` / `password` (7/7〜7/13の1週間分の固定労働データが紐付いています)
6. **ユーザー6（一般スタッフ）**: `user6@example.com` / `password` (7/7〜7/13の1週間分の固定労働データが紐付いています)

### 📈 レポート統計機能の完全連動テスト
「ユーザー1」に対して、仕様書・指示書に記載されている**「マイ勤怠レポート画面」の予測値と1ミリの狂いもないデータ**を自動生成します。
- **過去5ヶ月分（2月〜6月：各月平日15日間）**: 計算エンジンが月ごとに正しく機能していることを証明するため、総残業時間10時間（36,000秒）を2月〜5月へ**クリーンにランダム分散配置**。
- **当月（7月）の17日間パターン精密シミュレーション**:
  - 通常勤務 (09:00 - 18:00) × 13日間 (実働8h)
  - **遅刻判定データ** (09:30 - 18:00) × 2回（始業09:00超過の警告を検証）
  - **早退判定データ** (09:00 - 17:00) × 1回（終業18:00前退勤の警告を検証）
  - **長時間労働・残業データ** (08:00 - 21:00) × 1回（実働12h・1日10時間超えの警告、および4時間分の残業の同時加算を検証）
  
👉 これにより、「ユーザー1」でログインしてレポートを開くと、指示書予測値である **総労働時間「744h 0m」・総残業時間「10h 0m」・平均労働時間「8h 5m」・遅刻2回・早退1回・長時間1日** が一発で完全に再現されます。

### 📝 新規登録テスト用のお手本データ（手動作成用）
- **アカウント名**: テスト
- **メールアドレス**: test@testemail.com
- **パスワード**: password
- **確認用パスワード**: password

---


## 📊 データベース設計（ER図 ＆ テーブル詳細仕様）

仕様書の要件、および実運用に耐えうるデータ整合性を担保するため、以下のリレーションシップと制約（ユニーク制約・外部キー制約）を厳格に実装しています。GitHub上では以下のMermaidコードが美しいグラフィカルなER図として動的に自動描画されます。

```mermaid
erdiagram
    users ||--o{ attendances : "1 : 多"
    users ||--o{ attendance_requests : "1 : 多"
    attendances ||--o{ break_times : "1 : 多"

    users {
        bigint id PK "自動インクリメント"
        string name "ユーザー名"
        string email "メールアドレス (ユニーク)"
        string password "ハッシュ化パスワード"
        boolean is_admin "管理者フラグ (1:管理者 / 0:一般)"
        timestamp email_verified_at "メール認証日時"
    }

    attendances {
        bigint id PK "自動インクリメント"
        bigint user_id FK "users.id 参照"
        date date "勤務日"
        time clock_in "出勤打刻時刻"
        time clock_out "退勤打刻時刻 (任意)"
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
        date date "対象勤務日"
        time clock_in "申請出勤時刻"
        time clock_out "申請退勤時刻"
        json break_times "申請休憩時間配列 (JSON形式)"
        string remarks "必須：修正理由・備考"
        string status "承認ステータス (pending / approved)"
    }
```

---

### 📝 マイグレーション（Migration）を用いたスキーマ管理仕様

本プロジェクトでは、生SQLでのテーブル管理を完全に廃止し、チーム開発や本番環境への安全なデプロイを可能にするため、Laravelの **`Migration（マイグレーション）`** 機能を用いてスキーマ（テーブル構成）のバージョン管理を100%実施しています。

#### 🛡️ 1. マイグレーションファイルの役割と構成
`database/migrations/` 配下にある以下の各定義ファイルを実行することで、データベースの物理構造がコードベースで完全に復元されます。
- **`*_create_users_table.php`**: アカウント情報を保持する `users` テーブルを定義。
- **`*_create_attendances_table.php`**: `user_id` に対する外部キー制約（`foreignId()->constrained()`）および、1日1データ制限のための**「複合ユニークキー（`unique(['user_id', 'date'])`）」**をマイグレーション内で厳密に指定。
- **`*_create_break_times_table.php`**: 勤怠データに1対多で紐付く休憩時間テーブルを定義。親レコード削除時に連動する `cascadeOnDelete()` 制約をコード上で保証。
- **`*_create_attendance_requests_table.php`**: 修正申請データを一時保存するテーブルを定義。備考用の `remarks` カラムは、空欄でのインサートを防ぐため、敢えて `nullable()` を付与しない `NOT NULL` 設計に統一。

#### 🔄 2. 環境構築時のリセットコマンドの仕組み
環境構築時に実行する **`./vendor/bin/sail artisan migrate:fresh --seed`** は、以下の処理を一瞬で全自動実行する強力なシステムコマンドです。
1. **`migrate:fresh`**: データベース内にある古いテーブル構造や、不整合が起きた過去のテストデータを一度すべて根こそぎ完全消去（Drop）します。
2. その後、最新のマイグレーションファイルを上から順番に読み込み、まっさらな状態で完璧なテーブル群を再構築します。
3. **`--seed`**: 再構築が完了した直後、`DatabaseSeeder` を自動起動し、以下の「検証用固定アカウントおよび過去の大量実績データ」を瞬時にデータベースに注入します。

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
| 申請一覧画面（一般） | `/stamp_correction_request/list`| GET | `AttendanceController` | `requestList` | 必須 | 自身が提出した修正申請のタブ切り替え一覧 |
| マイ勤怠レポート（一般） | `/attendance/report` | GET | `AttendanceReportController` | `index` | 必須 | 過去6ヶ月の実労働・残業時間、今月の異常検知の完全自動集計 |
| ログイン画面（管理者） | `/admin/login` | GET/POST | `Admin\AttendanceController`| `showLogin` / `login` | 制限なし | 管理者専用のログイン認証 |
| 勤怠一覧画面（管理者） | `/admin/attendance/list` | GET | `Admin\AttendanceController`| `list` | 必須（管理）| 当日の全スタッフ勤怠日次一覧 |
| 勤怠詳細画面（管理者） | `/admin/attendance/{id}` | GET/POST | `Admin\AttendanceController`| `detail` / `updateDetail` | 必須（管理）| 管理者によるスタッフ勤怠データの直接修正・更新 |
| スタッフ一覧画面（管理者） | `/admin/staff/list` | GET | `Admin\AttendanceController`| `staffList` | 必須（管理）| 登録されている全一般スタッフのリスト閲覧 |
| スタッフ別勤怠一覧（管理者）| `/admin/attendance/staff/{id}` | GET/POST | `Admin\AttendanceController`| `staffAttendance` / `exportCsv` | 必須（管理）| 選択したスタッフの月次一覧閲覧、および時間自動計算CSV出力 |
| 申請一覧画面（管理者） | `/admin/stamp_correction_request/list`| GET | `Admin\AttendanceController`| `requestList` | 必須（管理）| 全スタッフから提出された承認待ち申請の一覧表示 |
| 修正申請承認画面（管理者） | `/admin/stamp_correction_request/approve/{id}`| GET/POST | `Admin\AttendanceController`| `approveView` / `approveAction`| 必須（管理）| 申請内容の確認、および承認アクション |

---

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
