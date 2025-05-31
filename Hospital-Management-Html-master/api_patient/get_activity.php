<?php
// simple stub so JS fetch won’t 404
header('Content-Type: application/json; charset=utf-8');
echo json_encode([]); // always returns an empty list
