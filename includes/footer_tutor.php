<body>
  <div class="wrapper">
    <div class="sidebar"> ... </div>
    <div class="top-header"> ... </div>
    <div class="content"> ... </div>
    <footer>
      &copy; <?= date('Y') ?> Creative Cloud Web - Tutor Dashboard
    </footer>
  </div>
</body>
<style>
    body, html {
  height: 100%;
  margin: 0;
}

.wrapper {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  margin-left: 220px; /* leave space for sidebar */
}

.content {
  flex: 1; /* take all available space */
  padding: 24px;
  background: var(--bg);
  overflow-y: auto;
}

footer {
  padding:12px 24px;
  background:#fff7ed;
  color:#78350f;
  box-shadow:0 -2px 6px rgba(0,0,0,0.05);
  text-align:center;
}
</Style>
