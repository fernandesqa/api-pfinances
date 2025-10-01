<?php
    //HEADERS
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');

    //INSTANCIA O BANCO DE DADOS E A CONEXÃO
    $database = new Database();
    $db = $database->connection();

    // INSTANCIA O OBJETO USER
    $obUser = new User($db);

    //INSTANCIA O OBJETO BUDGET
    $obBudget = new Budget($db);

    //INSTACIA O OBJETO BUDGET_CONTROL
    $obBugetControl = new BudgetControl($db);

    //INSTACIA O OBJETO REVENUE
    $obRevenue = new Revenue($db);

    

?>