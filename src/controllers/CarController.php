<?php

namespace App\controllers;

use Twig\Environment;
use Envms\FluentPDO\Query;

class CarController
{
    protected $twig;
    protected $query1;
    protected $query2;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
        $pdo1 = new \PDO('mysql:host=localhost;dbname=cars_db', 'root', '');
        $pdo2 = new \PDO('mysql:host=localhost;dbname=parking_db', 'root', '');
        $this->query1 = new Query($pdo1);
        $this->query2 = new Query($pdo2);
    }

    public function index()
    {
        $autos = $this->query1->from('cars')->fetchAll();
        $parkplätze = $this->query2->from('parking_locations')->fetchAll();

        foreach ($autos as &$auto) {
            $auto['location'] = $this->query2->from('parking_locations')->where('id', $auto['parking_location_id'])->fetch();
        }

        echo $this->twig->render('car.twig', [
            'autos' => $autos,
            'parkplätze' => $parkplätze
        ]);
    }

    public function addCar($params)
    {
        $this->query1->insertInto('cars', [
            'make' => $params['make'],
            'model' => $params['model'],
            'parking_location_id' => $params['parking_location_id']
        ])->execute();
        header('Location: /');
    }

    public function assignParking($params)
    {
        $parkingLocation = $this->query2->from('parking_locations')->where('name', $params['parking_location_name'])->fetch();
        $car = $this->query1->from('cars')->where('make', $params['car_make'])->fetch();

        $this->query1->update('cars')->set(['parking_location_id' => $parkingLocation['id']])->where('id', $car['id'])->execute();
        header('Location: /');
    }

    public function deleteCar($params)
    {
        $this->query1->deleteFrom('cars')->where('id', $params['car_id'])->execute();
        header('Location: /');
    }

    public function deleteParking($params)
    {
        $this->query2->deleteFrom('parking_locations')->where('id', $params['parking_location_id'])->execute();
        header('Location: /');
    }

    public function addParking($params)
    {
        $this->query2->insertInto('parking_locations', [
            'name' => $params['name']
        ])->execute();
        header('Location: /');
    }
}
