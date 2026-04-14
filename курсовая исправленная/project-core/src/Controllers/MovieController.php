<?php

require_once __DIR__ . '/../Models/Movie.php';

class MovieController {

    private $db;
    private $movieModel;

    private const REDIRECT_HOME = 'Location: index.php';

    public function __construct($db) {
        $this->db = $db;
        $this->movieModel = new Movie($db);
    }

    public function renderHome() {

        $searchTitle = $_GET['search_title'] ?? '';
        $searchYear  = $_GET['search_year'] ?? '';

        $limit = 6;

        $page = isset($_GET['page']) ? (int)$page = $_GET['page'] : 1;

        if ($page < 1) {
            $page = 1;
        }

        $offset = ($page - 1) * $limit;

        $movies = [];
        $totalPages = 1;

        if (!empty($searchTitle) || !empty($searchYear)) {

            $allFoundMovies = $this->movieModel->searchAdvanced($searchTitle, $searchYear);

            $totalMovies = count($allFoundMovies);
            $totalPages = ceil($totalMovies / $limit);

            $movies = array_slice($allFoundMovies, $offset, $limit);

        } else {

            $movies = $this->movieModel->getPaginatedList($limit, $offset);

            $totalMovies = $this->movieModel->getTotalCount();
            $totalPages = ceil($totalMovies / $limit);
        }

        require_once __DIR__ . '/../../templates/main_page.php';
    }

    public function addRating() {

        if (!isset($_SESSION['user_id'])) {
            header(self::REDIRECT_HOME);
            exit;
        }

        $userId = $_SESSION['user_id'];
        $movieId = $_POST['movie_id'] ?? null;
        $rating  = $_POST['rating'] ?? null;

        if ($movieId && $rating) {

            if ($this->movieModel->hasUserRated($userId, $movieId)) {

                header(self::REDIRECT_HOME . '?route=home&error=already_rated');

            } else {

                $stmt = $this->db->prepare(
                    "INSERT INTO ratings (user_id, movie_id, rating) VALUES (?, ?, ?)"
                );

                $stmt->execute([$userId, $movieId, (int)$rating]);

                header(self::REDIRECT_HOME . '?route=home&success=rated');
            }

        } else {
            header(self::REDIRECT_HOME);
        }

        exit;
    }

    public function addToWatchlist() {

        if (!isset($_SESSION['user_id'])) {
            header(self::REDIRECT_HOME);
            exit;
        }

        $userId = $_SESSION['user_id'];
        $movieId = $_GET['id'] ?? null;

        if ($movieId) {

            if ($this->movieModel->isAlreadyInWatchlist($userId, $movieId)) {

                header(self::REDIRECT_HOME . '?route=home&error=already_in_watchlist');

            } else {

                $this->movieModel->addToWatchlist($userId, $movieId);

                header(self::REDIRECT_HOME . '?route=home&success=added_to_watchlist');
            }

        } else {
            header(self::REDIRECT_HOME);
        }

        exit;
    }

    public function showForm() {

        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            header(self::REDIRECT_HOME);
            exit;
        }

        if (isset($_GET['id'])) {
            $this->movieModel->getById($_GET['id']);
        }

        require_once __DIR__ . '/../../templates/admin_form.php';
    }

    public function saveMovie() {

        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            header(self::REDIRECT_HOME);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header(self::REDIRECT_HOME);
            exit;
        }

        $id = $_POST['id'] ?? null;
        $posterUrl = $_POST['old_poster'] ?? '';

        if (isset($_FILES['poster_file']) && $_FILES['poster_file']['error'] === UPLOAD_ERR_OK) {

            $fileTmpPath = $_FILES['poster_file']['tmp_name'];
            $fileName = $_FILES['poster_file']['name'];

            $newFileName = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "_", $fileName);

            $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/';

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $destPath = $uploadDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $destPath)) {
                $posterUrl = 'uploads/' . $newFileName;
            } else {
                die("Ошибка загрузки файла");
            }
        }

        $data = [
            'title' => $_POST['title'] ?? '',
            'description' => $_POST['description'] ?? '',
            'release_year' => (int)($_POST['release_year'] ?? 0),
            'poster_url' => $posterUrl
        ];

        if ($id) {

            $success = $this->movieModel->update(
                $id,
                $data['title'],
                $data['release_year'],
                $data['description'],
                $data['poster_url']
            );

        } else {
            $success = $this->movieModel->createMovie($data);
        }

        if ($success) {
            header(self::REDIRECT_HOME . '?route=admin&success=1');
        } else {
            echo "Ошибка БД";
            print_r($this->db->errorInfo());
        }

        exit;
    }

    public function deleteMovie() {

        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            header(self::REDIRECT_HOME);
            exit;
        }

        $id = $_GET['id'] ?? null;

        if ($id) {
            $this->movieModel->delete($id);
        }

        header(self::REDIRECT_HOME . '?route=admin');
        exit;
    }
}