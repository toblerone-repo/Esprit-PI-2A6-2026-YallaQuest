<?php
/* =====================================================================
   [WORKSHOP ARCHITECTURE] AuthController
   ---------------------------------------------------------------------
   Contrôleur d'authentification et de gestion utilisateur selon le
   Workshop UP WEB (Sessions et Sécurité PHP, parties 3 à 7).
   Hérite de UtilisateurC pour centraliser la logique métier
   et les requêtes préparées PDO.
   ===================================================================== */

require_once __DIR__ . '/utilisateurC.php';

class AuthController extends UtilisateurC
{
    // Hérite de :
    // - login(array $post): array
    // - logout(): void
    // - findByEmail(string $email): ?Utilisateur
    // - findById(int $id): ?Utilisateur
    // - getAllUsers(): array
    // - countByRole(string $role): int
    // - countAllUsers(): int
    // - getCsrfToken(): string
    // - verifyCsrfToken(?string $token): bool
    // - isAuthenticated(): bool
    // - hasRole(string $role): bool
    // - requireAuth(): void
    // - requireRole(string $role): void
}
