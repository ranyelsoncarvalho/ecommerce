<?php if(!class_exists('Rain\Tpl')){exit;}?><!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>
    Produtos da Categoria: <?php echo htmlspecialchars( $category["descategory"], ENT_COMPAT, 'UTF-8', FALSE ); ?> <!--carregar o nome da categoria-->
  </h1>
  <ol class="breadcrumb">
    <li><a href="/admin"><i class="fa fa-dashboard"></i> Home</a></li>
    <li><a href="/admin/categories">Categorias</a></li>
    <li><a href="/admin/categories/<?php echo htmlspecialchars( $category["idcategory"], ENT_COMPAT, 'UTF-8', FALSE ); ?>"><?php echo htmlspecialchars( $category["descategory"], ENT_COMPAT, 'UTF-8', FALSE ); ?></a></li> <!--fazer o menu das categorias-->
    <li class="active"><a href="/admin/categories/<?php echo htmlspecialchars( $category["idcategory"], ENT_COMPAT, 'UTF-8', FALSE ); ?>/products">Produtos</a></li>
  </ol>
</section>

<!-- Main content -->
<section class="content">

    <div class="row">
        <div class="col-md-6"> <!--divisao para apresentar no site-->
            <div class="box box-primary">
                <div class="box-header with-border">
                <h3 class="box-title">Todos os Produtos</h3> <!--relacao de todos os produtos-->
                </div>
                <!-- /.box-header -->
                <!-- form start -->
                <div class="box-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                            <th style="width: 10px">#</th>
                            <th>Nome do Produto</th>
                            <th style="width: 240px">&nbsp;</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $counter1=-1;  if( isset($productsNotRelated) && ( is_array($productsNotRelated) || $productsNotRelated instanceof Traversable ) && sizeof($productsNotRelated) ) foreach( $productsNotRelated as $key1 => $value1 ){ $counter1++; ?> <!--relacionar todo os produtos que nao estao relacionados a categoria-->
                            <tr>
                            <td><?php echo htmlspecialchars( $value1["idproduct"], ENT_COMPAT, 'UTF-8', FALSE ); ?></td>
                            <td><?php echo htmlspecialchars( $value1["desproduct"], ENT_COMPAT, 'UTF-8', FALSE ); ?></td>
                            <td>
                                <a href="/admin/categories/<?php echo htmlspecialchars( $category["idcategory"], ENT_COMPAT, 'UTF-8', FALSE ); ?>/products/<?php echo htmlspecialchars( $value1["idproduct"], ENT_COMPAT, 'UTF-8', FALSE ); ?>/add" class="btn btn-primary btn-xs pull-right"><i class="fa fa-arrow-right"></i> Adicionar</a>
                            </td>
                            </tr>
                            <?php } ?>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="box box-success">
                <div class="box-header with-border">
                <h3 class="box-title">Produtos na Categoria <?php echo htmlspecialchars( $category["descategory"], ENT_COMPAT, 'UTF-8', FALSE ); ?></h3> <!--carregar todos os produtos que estao relacionados a categoria atual-->
                </div>
                <!-- /.box-header -->
                <!-- form start -->
                <div class="box-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                            <th style="width: 10px">#</th>
                            <th>Nome do Produto</th>
                            <th style="width: 240px">&nbsp;</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $counter1=-1;  if( isset($productsRelated) && ( is_array($productsRelated) || $productsRelated instanceof Traversable ) && sizeof($productsRelated) ) foreach( $productsRelated as $key1 => $value1 ){ $counter1++; ?>

                            <tr>
                            <td><?php echo htmlspecialchars( $value1["idproduct"], ENT_COMPAT, 'UTF-8', FALSE ); ?></td>
                            <td><?php echo htmlspecialchars( $value1["desproduct"], ENT_COMPAT, 'UTF-8', FALSE ); ?></td>
                            <td>
                                <a href="/admin/categories/<?php echo htmlspecialchars( $category["idcategory"], ENT_COMPAT, 'UTF-8', FALSE ); ?>/products/<?php echo htmlspecialchars( $value1["idproduct"], ENT_COMPAT, 'UTF-8', FALSE ); ?>/remove" onclick="return confirm('Deseja realmente excluir este registro?')" class="btn btn-danger btn-xs pull-right"><i class="fa fa-arrow-left"></i> Remover</a>
                            </td>
                            </tr>
                            <?php } ?>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>        
    </div>

</section>
<!-- /.content -->
</div>
<!-- /.content-wrapper -->