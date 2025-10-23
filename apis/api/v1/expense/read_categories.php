<?php
    //HEADERS
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');

    //INSTANCIA O BANCO DE DADOS E A CONEXÃO
    $database = new Database();
    $db = $database->connection();

    //INSTANCIA O OBJETO EXPENSE_CATEGORY
    $obExpenseCategory = new ExpenseCategory($db);

    $result = $obExpenseCategory->getCategories();

    // ARRAY CATEGORIES
    $categories_arr = array();
    $categories_arr['data'] = array();

    while($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        $categories_item = array(
            'id' => $Expense_Category_ID,
            'description' => $Expense_Category_Description
        );

        array_push($categories_arr['data'], $categories_item);
    }

    //CONVERTE EM JSON
    http_response_code(200);
    echo json_encode($categories_arr);
?>