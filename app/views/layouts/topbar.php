<div class="main-panel">
  <div class="main-header">
    <div class="main-header-logo">
      <div class="logo-header" data-background-color="dark">
        <a href="index.html" class="logo">
          <img src="../assets/img/kaiadmin/elevage.jfif" alt="navbar brand" class="navbar-brand" height="20" />
        </a>
        <div class="nav-toggle">
          <button class="btn btn-toggle toggle-sidebar"><i class="gg-menu-right"></i></button>
          <button class="btn btn-toggle sidenav-toggler"><i class="gg-menu-left"></i></button>
        </div>
        <button class="topbar-toggler more"><i class="gg-more-vertical-alt"></i></button>
      </div>
    </div>
    <nav class="navbar navbar-header navbar-header-transparent navbar-expand-lg border-bottom">
      <div class="container-fluid">
        <nav class="navbar navbar-header-left navbar-expand-lg navbar-form nav-search p-0 d-none d-lg-flex">
          <div class="input-group">
            <div class="input-group-prepend">
              <button type="submit" class="btn btn-search pe-1"><i class="fa fa-search search-icon"></i></button>
            </div>
            <input type="text" placeholder="Search ..." class="form-control" />
          </div>
        </nav>
        <li class="nav-item topbar-user dropdown hidden-caret">
          <a class="dropdown-toggle profile-pic" data-bs-toggle="dropdown" href="#" aria-expanded="false">
            <span class="profile-username">
              <span class="op-7">Connecter en tant que</span>
              <span class="fw-bold"><?= $_SESSION['username'] ?? 'Utilisateur' ?></span>
            </span>
          </a>
          <ul class="dropdown-menu dropdown-user animated fadeIn">
            <div class="dropdown-user-scroll scrollbar-outer">
              <li>
                <div class="user-box">
                  <div class="u-text">
                    <h4><?= $_SESSION['username'] ?? '  ' ?></h4>
                    <p class="text-muted"><?= $_SESSION['email'] ?? 'email@example.com' ?></p>
                    <a href="/profile" class="btn btn-xs btn-secondary btn-sm">Voir profil</a>
                  </div>
                </div>
              </li>
              <li>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="/profile">Mon profil</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="/logout">Déconnexion</a>
              </li>
            </div>
          </ul>
        </li>
      </div>
    </nav>
  </div>
