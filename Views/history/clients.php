                                                <!-- Vue de la liste client admin -->
<?php
$title = $title = 'Admin - Historique clients';
?>

<section>

    <div class="text-center">
        <h4 class="text-center text-center text-center mb-5 border border-light  fs-5 fst-italic text-danger rounded col-12 col-md-6 col-lg-4 mx-auto p-2">Historique clients</h4>
    </div>

    <div class="admin-reservation col-12 col-sm-10 col-md-8 col-lg-10 mx-auto">
        <p class='text-center text-warning'>Attention ! En cas de suppression d'un client, toutes les données relatives au client ainsi que ses transports seront définitivement supprimées !</p>
        <div class="table-responsive">
            <table class="table table-sm table-borderless border border-0  table-dark text-center align-middle mb-5">
                <thead class="align-middle" id="thead">
                    <tr>
                        <th scope="col" class="text-danger">Nom</th>
                        <th scope="col" class="text-danger">Prénom</th>
                        <th scope="col" class="text-danger">Email</th>
                        <th scope="col" class="text-danger">Téléphone</th>  
                    </tr>
                </thead>
                <tbody id="">
                    <?php
                        // Parcours les jointures complètes
                        foreach($list as $val) { ?>
                                <tr>
                                    <td class="data"><?php echo $val->name; ?></td>
                                    <td class="data"><?php echo $val->surname; ?></td>
                                    <td class="data"><?php echo $val->email; ?></td>
                                    <td class="data"><?php echo $val->tel; ?></td>
                                    <td class="flexTd"><a href="index.php?controller=historyClients&action=updateClient&token=<?php echo trim($_SESSION['token']);?>&id=<?php echo $val->idClient_histo;?>" class="btn btn-transparent bg-dark text-success"><i class='fi fi-tr-pen-circle'></i></a>
                                    <a href="index.php?controller=historyClients&action=deleteClient&token=<?php echo trim($_SESSION['token']);?>&id=<?php echo $val->idClient_histo;?>" class="btn btn-transparent  bg-dark text-danger"><i class='fi fi-sr-cross-circle'></i></a></td>
                                </tr>
                    <?php }  ?>
                   
                </tbody>
            </table>
        </div>
    </div>
</section>