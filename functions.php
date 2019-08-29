<?php

function formatPrice(float $vlPrice) { //formatar o preco dos produtos que estao carregados no banco

    return number_format($vlPrice, 2, ",", ".");

} 

//essa funcao sera dentro do template


?>