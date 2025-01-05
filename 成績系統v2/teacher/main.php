<?php
session_start();
ob_start();


$teacherName = $_SESSION['name'] ?? '教師';
?>
<!DOCTYPE html>
<html lang="zh-Hant">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>教師主選單</title>
    <!-- Bootstrap 5 CSS -->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
    />
    <style>
      body {
        background-color: #eaf6ff; /* 淺藍色背景 */
      } 
      .card {
        border-radius: 12px; /* 圓角 */
      }
      .card:hover {
        transform: translateY(-2px);
        transition: 0.3s;
      }
      .navbar-brand {
        font-weight: 600;
      }
    </style>
  </head>
  <body>
    <!-- 導覽列 (Navbar) -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
      <div class="container-fluid">
        <!-- 左側：標題 & 問候文字 -->
        <a class="navbar-brand" href="#">
          教師主選單
        </a>
        <span class="ms-2">
          <!-- 哈囉！XX教師 -->
          哈囉！<?php echo htmlspecialchars($teacherName); ?> 
        </span>

        <!-- 切換按鈕(手機版) -->
        <button
          class="navbar-toggler"
          type="button"
          data-bs-toggle="collapse"
          data-bs-target="#navbarSupportedContent"
          aria-controls="navbarSupportedContent"
          aria-expanded="false"
          aria-label="切換導覽列"
        >
          <span class="navbar-toggler-icon"></span>
        </button>

        <!-- 右側：登出 -->
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
          <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
            <!-- 只有「登出」一項 -->
            <li class="nav-item">
              <a class="nav-link text-danger" href="logout.php">登出</a>
            </li>
          </ul>
        </div>
      </div>
    </nav>

    <!-- 主內容：大方格 (2 x 2) -->
    <div class="container py-5">
      <div class="row row-cols-1 row-cols-md-2 g-4">
        <!-- 個人資料管理 -->
        <div class="col">
          <a href="teacher.html" class="text-decoration-none">
            <div class="card h-100 shadow-sm text-center">
              <div class="card-body d-flex align-items-center justify-content-center">
                <h4 class="card-title mb-0">個人資料管理</h4>
              </div>
            </div>
          </a>
        </div>
        <!-- 建立、修改課程 -->
        <div class="col">
          <a href="course.html" class="text-decoration-none">
            <div class="card h-100 shadow-sm text-center">
              <div class="card-body d-flex align-items-center justify-content-center">
                <h4 class="card-title mb-0">建立、修改課程</h4>
              </div>
            </div>
          </a>
        </div>
        <!-- 成績登入 -->
        <div class="col">
          <a href="score.html" class="text-decoration-none">
            <div class="card h-100 shadow-sm text-center">
              <div class="card-body d-flex align-items-center justify-content-center">
                <h4 class="card-title mb-0">成績登入</h4>
              </div>
            </div>
          </a>
        </div>
        <!-- 獎懲登入 -->
        <div class="col">
          <a href="record.html" class="text-decoration-none">
            <div class="card h-100 shadow-sm text-center">
              <div class="card-body d-flex align-items-center justify-content-center">
                <h4 class="card-title mb-0">獎懲登入</h4>
              </div>
            </div>
          </a>
        </div>
      </div>
    </div>

    <!-- Bootstrap 5 JS (含 Popper) -->
    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
    ></script>
  </body>
</html>