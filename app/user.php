<?php

require_once('functions.php');

function is_connected(){
    // Check if user is connected
    if(isset($_SESSION['id'])){
        $id_user = $_SESSION['id'];
        return true;
    }else{
        return false;
    }
}

function read_user(){
    $id_user=$_SESSION["id"];
    $pdo = bdd_connection();
    
    $request= "SELECT * FROM user,universe,city
    WHERE user.id_user='".$id_user."'
    AND user.id_city=city.id_city
    AND user.id_universe=universe.id_universe";
    $result = $pdo->query($request) or die (print_r($pdo->errorInfo())."Erreur : la requête a échoué.");

    if($result->rowCount()<1){
        echo ("Erreur : aucun utilisateur trouvé.");
    }else{
        while($row = $result->fetch(PDO::FETCH_ASSOC)){
            
            echo <<<HTML
            <table class="table">
                <tr>
                    <th class="table_item">Nom</th>
                    <td class="table_item">{$row["lastname_user"]}</td>
                </tr>
                <tr>
                    <th class="table_item">Prénom</th>
                    <td class="table_item">{$row["firstname_user"]}</td>
                </tr>
                <tr>
                    <th class="table_item">Pseudo</th>
                    <td class="table_item">{$row["username_user"]}</td>
                </tr>
                <tr>
                    <th class="table_item">Mot de passe</th>
                    <td class="table_item">{$row["password_user"]}</td>
                </tr>
                <tr>
                    <th class="table_item">Email</th>
                    <td class="table_item">{$row["email_user"]}</td>
                </tr>
                <tr>
                    <th class="table_item">Téléphone mobile</th>
                    <td class="table_item">{$row["mobile_user"]}</td>
                </tr>
                <tr>
                    <th class="table_item">Expérience</th>
                    <td class="table_item">{$row["finalscore_user"]}</td>
                </tr>
                <tr>
                    <th class="table_item">Univers</th>
                    <td class="table_item">{$row["name_universe"]}<hr>{$row["description_universe"]}</td>
                </tr>
                <tr>
                    <th class="table_item">Ville</th>
                    <td class="table_item">{$row["name_city"]}</td>
                </tr>
            </table>
HTML;
            
        }
    }
}

function create_user(){
    
    $pdo = bdd_connection();

    if(isset($_POST["lastname_user"], $_POST["firstname_user"], $_POST["email_user"], $_POST["mobile_user"], $_POST["username_user"], $_POST["password_user"], $_POST["id_universe"], $_POST["name_city"], $_POST["postcode_city"], $_POST["country_city"])){
        $id_city="";
        /* Add city*/
        $request = 'SELECT * FROM city';
        $result = $pdo->query($request) or die ("Erreur : la requête a échoué.");
        while($row = $result->fetch(PDO::FETCH_ASSOC)){
            if($row["postcode_city"]==$_POST["postcode_city"]){
                $id_city=$row["id_city"];
                $_POST["postcode_city"]=$row["postcode_city"];
                $_POST["name_city"]=$row["name_city"];
                $_POST["country_city"]=$row["country_city"];
            }
        }
        if(empty($id_city)){
            $id_city = "NULL";
            $request = 'INSERT INTO city VALUES ('.$id_city.',"'.$_POST['name_city'].'","'.$_POST['postcode_city'].'","'.$_POST['country_city'].'")';
            $pdo->exec($request) or die ($request." fail. <a href='form_user.php'>Retry</a>");
        }
        
        /* Get last city */
        if(empty($id_city) || $id_city=="NULL"){
            $request = 'SELECT id_city FROM city WHERE postcode_city="'.$_POST["postcode_city"].'"';
            $result = $pdo->query($request) or die ("Erreur : la requête get last city a échoué.".print_r($pdo->errorInfo()));
            while($row = $result->fetch(PDO::FETCH_ASSOC)){
                $id_city = $row["id_city"];
            }
        }
        
        /* Add user */
        $request = 'SELECT * FROM user';
        $result = $pdo->query($request) or die ("Erreur : la requête a échoué.");
        while($row = $result->fetch(PDO::FETCH_ASSOC)){
            if($row["username_user"]==$_POST["username_user"]){
                redirect("form_user.php?error=name");
            }
        }
        $request = 'INSERT INTO user VALUES (NULL,"'.$_POST['lastname_user'].'","'.$_POST['firstname_user'].'","'.$_POST['email_user'].'","'.$_POST['mobile_user'].'","'.$_POST['username_user'].'","'.password_hash($_POST['password_user'], PASSWORD_BCRYPT).'",0,"'.$_POST['id_universe'].'","'.$id_city.'")';
        $pdo->exec($request) or die ($request." fail. <a href='form_user.php'>Retry</a>");
        
        return TRUE;

    }else{

        return FALSE;
    }
}

function login_user(){
    $pdo = bdd_connection();
    
    if(isset($_POST["username_user"], $_POST["password_user"])){
        $request= "SELECT * FROM user";
        $result = $pdo->query($request);

        $id_user = null;
        while($row = $result->fetch(PDO::FETCH_ASSOC)){
            if($row["username_user"]==$_POST["username_user"]){
                if(password_verify($_POST["password_user"], $row["password_user"])){
                    $id_user=$row["id_user"];
                    $_SESSION["id"]=$row["id_user"];
                    $_SESSION["name"]=$row["username_user"];
                }
            }
        }

        if($id_user==NULL){
            redirect("form_login.php?error=true");
            return FALSE;
        }else{
            return TRUE;
        }
    }else{
        return FALSE;
    }
}

function logout_user(){
    if(is_connected()){
        session_destroy();
    }
    return TRUE;
}

?>