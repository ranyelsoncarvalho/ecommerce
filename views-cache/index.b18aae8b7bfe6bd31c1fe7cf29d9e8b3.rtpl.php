<?php if(!class_exists('Rain\Tpl')){exit;}?><!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
            Painel Administrativo, bem-vindo <?php echo getUserName(); ?>! <br>
            <h3>Usuarios cadastrados: <?php echo getTotalUsers(); ?></h3>
            <h3>Categorias cadastradas: <?php echo getTotalCategories(); ?></h3> 
            <h3>Produtos cadastrados: <?php echo getTotalProducts(); ?></h3>
            <h3>Total de pedidos cadastrados: <?php echo getTotalOrders(); ?></h3>
            <small>Graficos de pedidos por status</small> <br>
          </h1>
        </section>
    
        <!-- Main content -->
        <section class="content">
    
          <!-- Your Page Content Here -->
    
        </section>
        <!-- /.content -->
      </div>
      <!-- /.content-wrapper -->