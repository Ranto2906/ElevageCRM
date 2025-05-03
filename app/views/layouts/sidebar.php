<!-- Sidebar -->
<div class="sidebar" data-background-color="dark">
  <div class="sidebar-logo">
    <div class="logo-header" data-background-color="dark">
      <a href="/home" class="logo">
        <img src="../assets/img/kaiadmin/elevage.jfif" alt="navbar brand" class="navbar-brand" width="100" height="60"/>
      </a>
      <div class="nav-toggle">
        <button class="btn btn-toggle toggle-sidebar"><i class="gg-menu-right"></i></button>
        <button class="btn btn-toggle sidenav-toggler"><i class="gg-menu-left"></i></button>
      </div>
      <button class="topbar-toggler more"><i class="gg-more-vertical-alt"></i></button>
    </div>
  </div>
  <div class="sidebar-wrapper scrollbar scrollbar-inner">
    <div class="sidebar-content">
      <ul class="nav nav-secondary">
        <li class="nav-item active">
          <a href="/home"><i class="fas fa-home"></i><p>Accueil</p></a>
        </li>
        <li class="nav-section">
          <span class="sidebar-mini-icon"><i class="fa fa-ellipsis-h"></i></span>
          <h4 class="text-section">Administration</h4>
        </li>
        <li class="nav-item"><a href="/admin"><i class="fas fa-cog"></i><p>Admin</p></a></li>
        <li class="nav-item">
          <a data-bs-toggle="collapse" href="#gestion"><i class="fas fa-database"></i><p>Gestion des donnees</p><span class="caret"></span></a>
          <div class="collapse" id="gestion">
            <ul class="nav nav-collapse">
              <li><a href="/client"><span class="sub-item">Clients</span></a></li>
              <li><a href="/action-client"><span class="sub-item">Actions clients</span></a></li>
              <li><a href="/reaction-client"><span class="sub-item">Reactions clients</span></a></li>
              <li><a href="/reaction-impact"><span class="sub-item">Impact des reactions</span></a></li>
            </ul>
          </div>
        </li>
        <li class="nav-item">
          <a data-bs-toggle="collapse" href="#importExport"><i class="fas fa-file-import"></i><p>Import/Export</p><span class="caret"></span></a>
          <div class="collapse" id="importExport">
            <ul class="nav nav-collapse">
              <li><a href="#"><span class="sub-item">Import CSV</span></a></li>
              <li><a href="#"><span class="sub-item">Export CSV</span></a></li>
              <li><a href="#"><span class="sub-item">Export PDF</span></a></li>
            </ul>
          </div>
        </li>
        <li class="nav-item"><a href="/office"><i class="fas fa-building"></i><p>Office</p></a></li>
        <li class="nav-item"><a href="/logout"><i class="fas fa-sign-out-alt"></i><p>Déconnexion</p></a></li>
      </ul>
    </div>
  </div>
</div>
<!-- End Sidebar -->
