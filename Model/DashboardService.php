<?php

declare(strict_types=1);

class DashboardService
{
    private Model $model;

    public function __construct()
    {
        $this->model = new Model();
    }

    private function hasDerniereConnexionColumn(): bool
    {
        $statement = $this->model->query(
            'SELECT COUNT(*) AS cnt
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?',
            ['utilisateurs', 'derniere_connexion']
        );

        $row = $statement->fetch();
        return (int) ($row['cnt'] ?? 0) > 0;
    }

    private function buildEmptyLoginTrend(int $days = 7): array
    {
        $trend = [];
        $today = new DateTime();

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = clone $today;
            $date->modify("-{$i} days");
            $trend[] = [
                'date' => $date->format('Y-m-d'),
                'label' => $date->format('M d'),
                'count' => 0,
            ];
        }

        return $trend;
    }

    /**
     * Get comprehensive user statistics
     */
    public function getUserStats(): array
    {
        $statement = $this->model->query(
            'SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN statut_compte = \'actif\' THEN 1 ELSE 0 END) as actif,
                SUM(CASE WHEN statut_compte = \'inactif\' THEN 1 ELSE 0 END) as inactif,
                SUM(CASE WHEN role = \'admin\' THEN 1 ELSE 0 END) as admin,
                SUM(CASE WHEN role = \'staff\' THEN 1 ELSE 0 END) as staff,
                SUM(CASE WHEN role = \'enseignant\' THEN 1 ELSE 0 END) as enseignant,
                SUM(CASE WHEN role = \'etudiant\' THEN 1 ELSE 0 END) as etudiant
            FROM utilisateurs'
        );

        $row = $statement->fetch();
        return [
            'total' => (int) ($row['total'] ?? 0),
            'actif' => (int) ($row['actif'] ?? 0),
            'inactif' => (int) ($row['inactif'] ?? 0),
            'admin' => (int) ($row['admin'] ?? 0),
            'staff' => (int) ($row['staff'] ?? 0),
            'enseignant' => (int) ($row['enseignant'] ?? 0),
            'etudiant' => (int) ($row['etudiant'] ?? 0),
        ];
    }

    /**
     * Get service requests statistics
     */
    public function getDemandesStats(): array
    {
        $statement = $this->model->query(
            'SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN statut = \'en_attente\' THEN 1 ELSE 0 END) as en_attente,
                SUM(CASE WHEN statut = \'en_cours\' THEN 1 ELSE 0 END) as en_cours,
                SUM(CASE WHEN statut = \'traite\' THEN 1 ELSE 0 END) as traite,
                SUM(CASE WHEN statut = \'rejete\' THEN 1 ELSE 0 END) as rejete,
                SUM(CASE WHEN WEEK(soumise_le) = WEEK(NOW()) AND YEAR(soumise_le) = YEAR(NOW()) THEN 1 ELSE 0 END) as this_week
            FROM demandes_service'
        );

        $row = $statement->fetch();
        return [
            'total' => (int) ($row['total'] ?? 0),
            'en_attente' => (int) ($row['en_attente'] ?? 0),
            'en_cours' => (int) ($row['en_cours'] ?? 0),
            'traite' => (int) ($row['traite'] ?? 0),
            'rejete' => (int) ($row['rejete'] ?? 0),
            'this_week' => (int) ($row['this_week'] ?? 0),
        ];
    }

    /**
     * Get appointments statistics
     */
    public function getRendezVousStats(): array
    {
        $statement = $this->model->query(
            'SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN statut = \'reserve\' THEN 1 ELSE 0 END) as reserve,
                SUM(CASE WHEN statut = \'confirme\' THEN 1 ELSE 0 END) as confirme,
                SUM(CASE WHEN statut = \'annule\' THEN 1 ELSE 0 END) as annule,
                SUM(CASE WHEN statut = \'termine\' THEN 1 ELSE 0 END) as termine,
                SUM(CASE WHEN date_debut > NOW() AND statut IN (\'reserve\', \'confirme\') THEN 1 ELSE 0 END) as upcoming
            FROM rendez_vous'
        );

        $row = $statement->fetch();
        return [
            'total' => (int) ($row['total'] ?? 0),
            'reserve' => (int) ($row['reserve'] ?? 0),
            'confirme' => (int) ($row['confirme'] ?? 0),
            'annule' => (int) ($row['annule'] ?? 0),
            'termine' => (int) ($row['termine'] ?? 0),
            'upcoming' => (int) ($row['upcoming'] ?? 0),
        ];
    }

    /**
     * Get document requests statistics
     */
    public function getDocumentsStats(): array
    {
        $statement = $this->model->query(
            'SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN statut = \'en_attente\' THEN 1 ELSE 0 END) as en_attente,
                SUM(CASE WHEN statut = \'en_validation\' THEN 1 ELSE 0 END) as en_validation,
                SUM(CASE WHEN statut = \'valide\' THEN 1 ELSE 0 END) as valide,
                SUM(CASE WHEN statut = \'rejete\' THEN 1 ELSE 0 END) as rejete,
                SUM(CASE WHEN statut = \'livre\' THEN 1 ELSE 0 END) as livre
            FROM demandes_document'
        );

        $row = $statement->fetch();
        return [
            'total' => (int) ($row['total'] ?? 0),
            'en_attente' => (int) ($row['en_attente'] ?? 0),
            'en_validation' => (int) ($row['en_validation'] ?? 0),
            'valide' => (int) ($row['valide'] ?? 0),
            'rejete' => (int) ($row['rejete'] ?? 0),
            'livre' => (int) ($row['livre'] ?? 0),
        ];
    }

    /**
     * Get events and registrations statistics
     */
    public function getEvenementsStats(): array
    {
        // Get event counts
        $eventsStatement = $this->model->query(
            'SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN statut = \'planifie\' THEN 1 ELSE 0 END) as planifie,
                SUM(CASE WHEN statut = \'ouvert\' THEN 1 ELSE 0 END) as ouvert,
                SUM(CASE WHEN statut = \'complet\' THEN 1 ELSE 0 END) as complet,
                SUM(CASE WHEN statut = \'termine\' THEN 1 ELSE 0 END) as termine,
                SUM(CASE WHEN statut = \'annule\' THEN 1 ELSE 0 END) as annule
            FROM evenements'
        );

        $eventsRow = $eventsStatement->fetch();

        // Get registration stats
        $regStatement = $this->model->query(
            'SELECT 
                COUNT(*) as total_inscriptions,
                SUM(CASE WHEN statut = \'inscrit\' THEN 1 ELSE 0 END) as inscrit,
                SUM(CASE WHEN statut = \'present\' THEN 1 ELSE 0 END) as present,
                SUM(CASE WHEN statut = \'absent\' THEN 1 ELSE 0 END) as absent
            FROM inscriptions_evenement'
        );

        $regRow = $regStatement->fetch();

        $totalInscriptions = (int) ($regRow['total_inscriptions'] ?? 0);
        $presentCount = (int) ($regRow['present'] ?? 0);
        $attendanceRate = $totalInscriptions > 0 ? round(($presentCount / $totalInscriptions) * 100, 2) : 0;

        return [
            'total_events' => (int) ($eventsRow['total'] ?? 0),
            'planifie' => (int) ($eventsRow['planifie'] ?? 0),
            'ouvert' => (int) ($eventsRow['ouvert'] ?? 0),
            'complet' => (int) ($eventsRow['complet'] ?? 0),
            'termine' => (int) ($eventsRow['termine'] ?? 0),
            'annule' => (int) ($eventsRow['annule'] ?? 0),
            'total_inscriptions' => $totalInscriptions,
            'inscrit' => (int) ($regRow['inscrit'] ?? 0),
            'present' => $presentCount,
            'absent' => (int) ($regRow['absent'] ?? 0),
            'attendance_rate' => $attendanceRate,
        ];
    }

    /**
     * Get activity and login statistics
     */
    public function getActivityStats(): array
    {
        if (!$this->hasDerniereConnexionColumn()) {
            return [
                'active_today' => 0,
                'active_this_week' => 0,
                'recent_logins' => [],
                'login_trend' => $this->buildEmptyLoginTrend(),
            ];
        }

        // Active users today
        $todayStatement = $this->model->query(
            'SELECT COUNT(*) as cnt FROM utilisateurs 
             WHERE DATE(derniere_connexion) = DATE(NOW())'
        );
        $todayRow = $todayStatement->fetch();

        // Active users this week
        $weekStatement = $this->model->query(
            'SELECT COUNT(*) as cnt FROM utilisateurs 
             WHERE WEEK(derniere_connexion) = WEEK(NOW()) 
             AND YEAR(derniere_connexion) = YEAR(NOW())'
        );
        $weekRow = $weekStatement->fetch();

        // Recent logins (last 10)
        $recentStatement = $this->model->query(
            'SELECT id, nom, prenom, email, derniere_connexion 
             FROM utilisateurs 
             WHERE derniere_connexion IS NOT NULL
             ORDER BY derniere_connexion DESC 
             LIMIT 10'
        );
        $recentLogins = $recentStatement->fetchAll();

        // Login trend data (past 7 days)
        $trendStatement = $this->model->query(
            'SELECT DATE(derniere_connexion) as date, COUNT(*) as count
             FROM utilisateurs
             WHERE derniere_connexion IS NOT NULL 
             AND DATE(derniere_connexion) >= DATE_SUB(NOW(), INTERVAL 6 DAY)
             GROUP BY DATE(derniere_connexion)
             ORDER BY DATE(derniere_connexion) ASC'
        );
        $trendData = $trendStatement->fetchAll();

        // Format trend data for charts
        $trend = [];
        $today = new DateTime();
        for ($i = 6; $i >= 0; $i--) {
            $date = clone $today;
            $date->modify("-{$i} days");
            $dateStr = $date->format('Y-m-d');
            $dateLabel = $date->format('M d');

            $count = 0;
            foreach ($trendData as $row) {
                if ($row['date'] === $dateStr) {
                    $count = (int) $row['count'];
                    break;
                }
            }

            $trend[] = [
                'date' => $dateStr,
                'label' => $dateLabel,
                'count' => $count,
            ];
        }

        return [
            'active_today' => (int) ($todayRow['cnt'] ?? 0),
            'active_this_week' => (int) ($weekRow['cnt'] ?? 0),
            'recent_logins' => $recentLogins,
            'login_trend' => $trend,
        ];
    }

    /**
     * Get all dashboard data at once
     */
    public function getAllStats(): array
    {
        return [
            'users' => $this->getUserStats(),
            'demandes' => $this->getDemandesStats(),
            'rendezvous' => $this->getRendezVousStats(),
            'documents' => $this->getDocumentsStats(),
            'evenements' => $this->getEvenementsStats(),
            'activity' => $this->getActivityStats(),
        ];
    }
}
