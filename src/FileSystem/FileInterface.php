<?php

namespace Brikphp\Console\FileSystem;

use DateTime;

/**
 * Interface FileInterface
 * Définit les opérations de gestion de fichiers.
 */
interface FileInterface
{
    /**
     * Crée un fichier.
     *
     * @return bool True si le fichier est créé avec succès, False sinon.
     */
    public function create(): bool;

    /**
     * Lit le contenu du fichier.
     *
     * @return string Contenu du fichier.
     */
    public function read(): string;

    /**
     * Écrit dans le fichier.
     *
     * @param  string $content Contenu à écrire dans le fichier.
     * @return int Le nombre d'octets écrits.
     */
    public function write(string $content): int;

    /**
     * Supprime le fichier.
     *
     * @return int Code de résultat (0 pour succès, >0 pour erreurs).
     */
    public function delete(): int;

    /**
     * Vérifie si le fichier existe.
     *
     * @return bool True si le fichier existe, False sinon.
     */
    public function exists(): bool;

    /**
     * Retourne le nom du fichier sans extension.
     *
     * @return string Nom du fichier.
     */
    public function getName(): string;

    /**
     * Retourne l'extension du fichier.
     *
     * @return string Extension du fichier.
     */
    public function getExt(): string;

    /**
     * Retourne la taille du fichier en octets.
     *
     * @return int Taille du fichier.
     */
    public function getSize(): int;

    /**
     * Retourne la date de dernière modification du fichier.
     *
     * @return DateTime Date de dernière modification.
     */
    public function getDate(): DateTime;
}
