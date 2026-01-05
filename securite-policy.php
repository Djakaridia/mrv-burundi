<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="apple-touch-icon" sizes="180x180" href="assets/favicon/apple-touch-icon.png" />
    <link rel="icon" type="image/png" sizes="32x32" href="assets/favicon/favicon-32x32.png" />
    <link rel="icon" type="image/png" sizes="16x16" href="assets/favicon/favicon-16x16.png" />
    <link rel="shortcut icon" type="image/x-icon" href="assets/favicon/favicon.png" />
    <title>Politique de Sécurité MRV Burundi</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', sans-serif;
            line-height: 1.6;
            color: #2c3e50;
            background-color: #f8fafb;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: white;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            margin-top: 20px;
            margin-bottom: 20px;
        }

        /* Header Styles */
        .header {
            text-align: center;
            padding: 30px 0;
            border-bottom: 3px solid #27ae60;
            margin-bottom: 40px;
            background: linear-gradient(135deg, #2b6644 0%, #35a564 100%);
            color: white;
            border-radius: 10px 10px 0 0;
            margin: -20px -20px 40px -20px;
            padding: 40px 20px;
        }

        .header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .header .subtitle {
            font-size: 1.2rem;
            font-weight: 300;
            opacity: 0.95;
        }

        /* Classification Banner */
        .classification {
            background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
            color: white;
            text-align: center;
            padding: 15px;
            font-weight: bold;
            font-size: 1.1rem;
            margin: -20px -20px 30px -20px;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 2px 10px rgba(243, 156, 18, 0.3);
        }

        /* Document Control Table */
        .document-control {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin: 30px 0;
            padding: 25px;
            background: #f8f9fa;
            border-radius: 10px;
            border-left: 4px solid #3498db;
        }

        .control-section h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 1.3rem;
            border-bottom: 2px solid #3498db;
            padding-bottom: 8px;
        }

        .control-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .control-table th {
            background: #34495e;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 600;
        }

        .control-table td {
            padding: 12px;
            border-bottom: 1px solid #ecf0f1;
        }

        .control-table tr:last-child td {
            border-bottom: none;
        }

        .control-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        /* TLP Classification */
        .tlp-section {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 10px;
            padding: 25px;
            margin: 30px 0;
        }

        .tlp-section h3 {
            color: #856404;
            margin-bottom: 15px;
            font-size: 1.4rem;
        }

        .tlp-levels {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }

        .tlp-level {
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid;
        }

        .tlp-white {
            background: #f8f9fa;
            border-left-color: #6c757d;
        }

        .tlp-green {
            background: #d4edda;
            border-left-color: #28a745;
        }

        .tlp-amber {
            background: #fff3cd;
            border-left-color: #ffc107;
        }

        .tlp-red {
            background: #f8d7da;
            border-left-color: #dc3545;
        }

        .tlp-level strong {
            font-size: 1.1rem;
        }

        /* Table of Contents */
        .toc {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 30px;
            margin: 40px 0;
            border-left: 4px solid #3498db;
        }

        .toc h2 {
            color: #2c3e50;
            margin-bottom: 25px;
            font-size: 1.8rem;
            text-align: center;
        }

        .toc-list {
            columns: 2;
            column-gap: 40px;
            list-style: none;
        }

        .toc-list li {
            break-inside: avoid;
            margin-bottom: 8px;
            padding: 8px 0;
            border-bottom: 1px dotted #bdc3c7;
        }

        .toc-list a {
            color: #3498db;
            text-decoration: none;
            font-weight: 500;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .toc-list a:hover {
            color: #2980b9;
            text-decoration: underline;
        }

        .page-number {
            background: #3498db;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: normal;
        }

        /* Main Content */
        .content-section {
            margin: 40px 0;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .content-section h1 {
            color: #27ae60;
            font-size: 2rem;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 3px solid #27ae60;
            position: relative;
        }

        .content-section h1::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: -3px;
            width: 60px;
            height: 3px;
            background: #2ecc71;
        }

        .content-section h2 {
            color: #2c3e50;
            font-size: 1.5rem;
            margin: 30px 0 15px;
            padding: 15px 0 10px;
            border-bottom: 2px solid #ecf0f1;
        }

        .content-section h3 {
            color: #34495e;
            font-size: 1.2rem;
            margin: 25px 0 12px;
            font-weight: 600;
        }

        .content-section p {
            margin-bottom: 15px;
            text-align: justify;
            line-height: 1.7;
        }

        .content-section ul {
            margin: 15px 0 15px 20px;
        }

        .content-section li {
            margin-bottom: 8px;
            line-height: 1.6;
        }

        /* Highlighted sections */
        .objectives-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin: 25px 0;
        }

        .objective-card {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
        }

        .objective-card h4 {
            font-size: 1.1rem;
            margin-bottom: 10px;
            font-weight: 600;
        }

        /* Glossary styles */
        .glossary {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 30px;
            margin: 40px 0;
        }

        .glossary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .glossary-term {
            background: white;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #27ae60;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .glossary-term strong {
            color: #27ae60;
            display: block;
            margin-bottom: 5px;
            font-size: 1rem;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .container {
                padding: 15px;
                margin: 10px;
            }

            .header h1 {
                font-size: 2rem;
            }

            .header .subtitle {
                font-size: 1rem;
            }

            .document-control {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .toc-list {
                columns: 1;
            }

            .objectives-grid {
                grid-template-columns: 1fr;
            }

            .glossary-grid {
                grid-template-columns: 1fr;
            }

            .content-section {
                padding: 20px;
            }
        }

        /* Print styles */
        @media print {
            body {
                font-size: 12pt;
                line-height: 1.4;
                color: #000;
            }

            .container {
                box-shadow: none;
                margin: 0;
                padding: 0;
            }

            .header {
                background: none !important;
                color: #000 !important;
                text-shadow: none !important;
            }

            .classification {
                background: none !important;
                color: #000 !important;
                border: 2px solid #000;
            }

            .content-section {
                page-break-inside: avoid;
                box-shadow: none;
                margin: 20px 0;
            }

            .content-section h1 {
                page-break-after: avoid;
            }
        }

        /* Smooth scrolling */
        html {
            scroll-behavior: smooth;
        }

        /* Highlight active section */
        .content-section:target {
            background: #f0f8f0;
            border-left: 4px solid #27ae60;
        }
    </style>
</head>

<body>
    <div class="container">

        <header class="header">
            <h1>POLITIQUE DE SÉCURITÉ</h1>
            <h2>Système MRV Burundi </h2>
        </header>

        <div class="document-control">
            <div class="control-section">
                <h3>Contrôle et révision des documents</h3>
                <table class="control-table">
                    <tr>
                        <th colspan="2">Vérification des documents</th>
                    </tr>
                    <tr>
                        <td>Auteur</td>
                        <td><strong>COSIT-MALI</strong></td>
                    </tr>
                    <tr>
                        <td>Propriétaire</td>
                        <td><strong>Office Burundais pour la Protection de l'Environnement (OBPE)</strong></td>
                    </tr>
                    <tr>
                        <td>Date de création</td>
                        <td><strong>10/12/2025</strong></td>
                    </tr>
                    <tr>
                        <td>Dernière révision par</td>
                        <td><strong>COSIT-MALI</strong></td>
                    </tr>
                    <tr>
                        <td>Date de la dernière révision</td>
                        <td><strong>10/12/2025</strong></td>
                    </tr>
                </table>
            </div>

            <div class="control-section">
                <h3>Gestion des versions</h3>
                <table class="control-table">
                    <tr>
                        <th colspan="2">Vérification des versions</th>
                    </tr>
                    <tr>
                        <td>Version</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Date d'approbation</td>
                        <td>-</td>
                    </tr>
                    <tr>
                        <td>Approuvé par</td>
                        <td>-</td>
                    </tr>
                    <tr>
                        <td>Description du changement</td>
                        <td>Dernière version</td>
                    </tr>
                </table>
            </div>
        </div>


        <div class="toc">
            <h2>Table des matières</h2>
            <ul class="toc-list">
                <li><a href="#introduction">1. INTRODUCTION <span class="page-number">1</span></a></li>
                <li><a href="#objectifs">2. OBJECTIFS DE SÉCURITÉ <span class="page-number">2</span></a></li>
                <li><a href="#champ-application">3. CHAMP D'APPLICATION <span class="page-number">3</span></a></li>
                <li><a href="#responsabilites">4. RESPONSABILITÉS ET RÔLES <span class="page-number">4</span></a></li>
                <li><a href="#super-admin">4.1. Super Administrateur <span class="page-number">5</span></a></li>
                <li><a href="#admin-chefs">4.2. Administrateurs et Chefs de Projet <span class="page-number">6</span></a></li>
                <li><a href="#utilisateurs">4.3. Utilisateurs et Contributeurs <span class="page-number">7</span></a></li>
                <li><a href="#equipe-securite">4.4. Équipe Sécurité <span class="page-number">8</span></a></li>
                <li><a href="#analyse-risques">5. ANALYSE DES RISQUES ET GESTION DES VULNÉRABILITÉS <span class="page-number">9</span></a></li>
                <li><a href="#identification-risques">5.1. Identification et Évaluation des Risques <span class="page-number">10</span></a></li>
                <li><a href="#gestion-vulnerabilites">5.2. Gestion des Vulnérabilités <span class="page-number">11</span></a></li>
                <li><a href="#mesures-techniques">6. MESURES DE SÉCURITÉ TECHNIQUES PAR MODULE <span class="page-number">12</span></a></li>
                <li><a href="#interface-admin">6.1. Interface d'Administration <span class="page-number">13</span></a></li>
                <li><a href="#collecte-donnees">6.2. Système de Collecte Automatique des Données <span class="page-number">14</span></a></li>
                <li><a href="#notifications">6.3. Système de Notifications <span class="page-number">15</span></a></li>
                <li><a href="#archivage">6.4. Système d'Archivage Électronique (ArchiPro) <span class="page-number">16</span></a></li>
                <li><a href="#site-vitrine">6.5. Site Web Vitrine <span class="page-number">17</span></a></li>
                <li><a href="#chatbots">6.6. Chatbots <span class="page-number">18</span></a></li>
                <li><a href="#fiches-dynamiques">6.7. Fiches Dynamiques <span class="page-number">19</span></a></li>
                <li><a href="#app-mobile">6.8. Application Mobile <span class="page-number">20</span></a></li>
                <li><a href="#mesures-organisationnelles">7. MESURES ORGANISATIONNELLES, FORMATION ET SENSIBILISATION <span class="page-number">21</span></a></li>
                <li><a href="#incidents">8. GESTION DES INCIDENTS DE SÉCURITÉ <span class="page-number">22</span></a></li>
                <li><a href="#detection">8.1. Détection et Signalement <span class="page-number">23</span></a></li>
                <li><a href="#reponse">8.2. Réponse et Remédiation <span class="page-number">24</span></a></li>
                <li><a href="#continuite">9. PLAN DE CONTINUITÉ D'ACTIVITÉ ET REPRISE APRÈS SINISTRE (PCA/PRA) <span class="page-number">25</span></a></li>
                <li><a href="#conformite">10. CONFORMITÉ, AUDITS ET MISE À JOUR DE LA POLITIQUE <span class="page-number">26</span></a></li>
                <li><a href="#gouvernance">11. GOUVERNANCE ET SUIVI <span class="page-number">27</span></a></li>
                <li><a href="#annexes">12. ANNEXES <span class="page-number">28</span></a></li>
            </ul>
        </div>

        <section id="introduction" class="content-section">
            <h1>1. INTRODUCTION</h1>
            <p>Le système MRV Burundi a pour vocation de suivre, reporter et vérifier les projets de développement durable au Burundi. Ce système, réparti sur plusieurs modules (interfaces d'administration, collecte automatique des données, notifications, archivage électronique, site web vitrine, chatbots, fiches dynamiques et application mobile), centralise et gère des données souvent sensibles (données environnementales, sociales, économiques, informations des utilisateurs, etc.) afin de garantir transparence, traçabilité et prise de décision éclairée. Ce document définit l'ensemble des règles, procédures et mesures de sécurité spécifiques, afin d'assurer la confidentialité, l'intégrité et la disponibilité des données, en conformité avec le cahier des charges et les exigences réglementaires.</p>
        </section>

        <section id="objectifs" class="content-section">
            <h1>2. OBJECTIFS DE SÉCURITÉ</h1>
            <div class="objectives-grid">
                <div class="objective-card">
                    <h4>Confidentialité</h4>
                    <p>Assurer que seules les personnes autorisées accèdent aux informations sensibles (ex. données environnementales, rapports, identités des utilisateurs).</p>
                </div>
                <div class="objective-card">
                    <h4>Intégrité</h4>
                    <p>Garantir l'exactitude et la cohérence des données, en empêchant toute modification non autorisée.</p>
                </div>
                <div class="objective-card">
                    <h4>Disponibilité</h4>
                    <p>Maintenir en permanence l'accès aux services critiques (saisie, reporting, consultation) malgré les incidents ou attaques.</p>
                </div>
                <div class="objective-card">
                    <h4>Traçabilité</h4>
                    <p>Enregistrer et archiver l'historique des actions effectuées sur l'ensemble des modules pour permettre des audits et analyses post-incident.</p>
                </div>
                <div class="objective-card">
                    <h4>Continuité d'Activité</h4>
                    <p>Mettre en œuvre des solutions de sauvegarde et un plan de reprise d'activité afin de limiter l'impact d'éventuels sinistres.</p>
                </div>
            </div>
        </section>

        <section id="champ-application" class="content-section">
            <h1>3. CHAMP D'APPLICATION</h1>
            <p>Cette politique s'applique à l'ensemble des composantes du système MRV Burundi ainsi qu'à :</p>
            <ul>
                <li>L'interface d'administration.</li>
                <li>Le système de collecte automatique.</li>
                <li>Le système de notifications.</li>
                <li>Le système d'archivage électronique (ArchiPro).</li>
                <li>Le site web vitrine.</li>
                <li>Les chatbots.</li>
                <li>Les fiches dynamiques.</li>
                <li>L'application mobile.</li>
            </ul>
            <p>Tous les acteurs internes (super administrateurs, administrateurs, chefs de projet, utilisateurs, contributeurs) et les prestataires externes impliqués doivent respecter les dispositions du présent document.</p>
        </section>

        <section id="responsabilites" class="content-section">
            <h1>4. RESPONSABILITÉS ET RÔLES</h1>

            <h2 id="super-admin">4.1. Super Administrateur</h2>
            <ul>
                <li>Création, modification et suppression des comptes utilisateurs.</li>
                <li>Attribution des droits d'accès selon le principe du moindre privilège.</li>
                <li>Configuration globale du système, y compris le lien pour la mise à jour du logiciel GIEC pour l'inventaire GES.</li>
                <li>Validation des mises à jour de sécurité pour l'ensemble des modules.</li>
            </ul>

            <h2 id="admin-chefs">4.2. Administrateurs et Chefs de Projet</h2>
            <ul>
                <li>Supervision quotidienne des opérations (gestion de contenus, validation des données, suivi des actions).</li>
                <li>Réalisation d'audits internes réguliers et veille à la conformité des pratiques.</li>
                <li>Gestion des mises à jour des modules (secteurs, groupes, actions d'atténuation, documents).</li>
            </ul>

            <h2 id="utilisateurs">4.3. Utilisateurs et Contributeurs</h2>
            <ul>
                <li>Respecter les règles d'accès et d'utilisation (gestion des mots de passe, authentification).</li>
                <li>Saisie des données en respectant les procédures (fiches dynamiques, collecte manuelle).</li>
                <li>Signalement immédiat de toute anomalie ou incident suspect.</li>
            </ul>

            <h2 id="equipe-securite">4.4. Équipe Sécurité</h2>
            <ul>
                <li>Surveillance continue (monitoring, détection d'intrusions, analyses de logs).</li>
                <li>Réalisation de scans de vulnérabilités, tests de pénétration et application des correctifs.</li>
                <li>Organisation de sessions de formation et de sensibilisation aux bonnes pratiques de sécurité.</li>
            </ul>
        </section>

        <section id="analyse-risques" class="content-section">
            <h1>5. ANALYSE DES RISQUES ET GESTION DES VULNÉRABILITÉS</h1>

            <h2 id="identification-risques">5.1. Identification et Évaluation des Risques</h2>
            <ul>
                <li><strong>Cartographie des Actifs :</strong> Recensement des serveurs, bases de données (MySQL), dispositifs IoT, applications web et mobiles, interfaces API.</li>
                <li><strong>Identification des Menaces :</strong> Attaques par injection SQL/NoSQL, XSS, CSRF, attaques DDoS, phishing, compromission des API, interception de communications, erreurs humaines.</li>
                <li><strong>Évaluation et Classement :</strong> Chaque actif est évalué (niveau de criticité) pour prioriser les mesures de protection.</li>
            </ul>

            <h2 id="gestion-vulnerabilites">5.2. Gestion des Vulnérabilités</h2>
            <ul>
                <li><strong>Scans de Vulnérabilités :</strong> Exécution régulière d'outils de scan sur l'ensemble des composants.</li>
                <li><strong>Mise à Jour et Patching :</strong> Application immédiate des correctifs pour Express.js, Prisma, React.js, React Native et autres bibliothèques.</li>
                <li><strong>Tests de Pénétration :</strong> Organisation périodique de pentests pour vérifier la robustesse globale.</li>
                <li><strong>Suivi et Documentation :</strong> Tenue d'un registre des vulnérabilités et des actions correctives mises en œuvre.</li>
            </ul>
        </section>

        <section id="mesures-techniques" class="content-section">
            <h1>6. MESURES DE SÉCURITÉ TECHNIQUES PAR MODULE</h1>

            <h2 id="interface-admin">6.1. Interface d'Administration</h2>
            <h3>Authentification :</h3>
            <ul>
                <li>Formulaire d'authentification sécurisé avec possibilité de mise en place d'une MFA.</li>
                <li>Gestion des sessions avec expiration automatique après inactivité.</li>
            </ul>
            <h3>Contrôle d'Accès et Gestion des Rôles :</h3>
            <ul>
                <li>Attribution stricte du moindre privilège (super admin, admin, contributeur).</li>
                <li>Journalisation détaillée de chaque action (connexion, modification, suppression).</li>
            </ul>
            <h3>Sécurisation Web :</h3>
            <p>Protection contre injections SQL, XSS, CSRF via validations côté serveur et utilisation de librairies spécialisées.</p>

            <h2 id="collecte-donnees">6.2. Système de Collecte Automatique des Données</h2>
            <h3>Objets Connectés (IoT) :</h3>
            <ul>
                <li>Communication via protocoles sécurisés (TLS/SSL).</li>
                <li>Authentification des dispositifs et vérification régulière de leur intégrité.</li>
            </ul>
            <h3>Web Scraping :</h3>
            <ul>
                <li>Mise en œuvre de contrôles pour respecter les règles d'accès aux sites cibles.</li>
                <li>Vérification des droits d'exploitation des données et journalisation des collectes.</li>
            </ul>
            <h3>Données Manuelles et Automatisées :</h3>
            <p>Validation automatique des données (détection des doublons, incohérences, erreurs).</p>

            <h2 id="notifications">6.3. Système de Notifications</h2>
            <h3>Canaux de Diffusion Sécurisés :</h3>
            <ul>
                <li>Envoi de notifications par WhatsApp, e-mail et notifications push via des API sécurisées.</li>
                <li>Vérification de l'authenticité de l'expéditeur et chiffrement des messages sensibles.</li>
            </ul>
            <h3>Suivi et Journalisation :</h3>
            <ul>
                <li>Conservation des logs de notifications envoyées pour audit et traçabilité.</li>
                <li>Mise en place d'alertes en cas d'échec ou de déviation dans la diffusion.</li>
            </ul>

            <h2 id="archivage">6.4. Système d'Archivage Électronique (ArchiPro)</h2>
            <h3>Centralisation et Sauvegarde :</h3>
            <ul>
                <li>Stockage centralisé des documents (rapports, études, contrats) sur des serveurs redondants.</li>
                <li>Sauvegardes régulières.</li>
            </ul>
            <h3>Sécurisation et Chiffrement :</h3>
            <ul>
                <li>Chiffrement des documents sensibles en transit et au repos.</li>
            </ul>
            <h3>Gestion des Versions et Accès :</h3>
            <ul>
                <li>Suivi des versions pour éviter l'utilisation de documents obsolètes.</li>
                <li>Accès réservé aux utilisateurs autorisés (modification et suppression par super admin).</li>
            </ul>

            <h2 id="site-vitrine">6.5. Site Web Vitrine</h2>
            <h3>Maintenance et Mises à Jour :</h3>
            <ul>
                <li>Application régulière des correctifs de sécurité pour tous les composants.</li>
                <li>Revues de sécurité périodiques et tests de vulnérabilité.</li>
            </ul>

            <h2 id="chatbots">6.6. Chatbots</h2>
            <h3>Sécurisation des API :</h3>
            <ul>
                <li>Authentification stricte et utilisation de tokens pour sécuriser les échanges.</li>
                <li>Chiffrement des communications entre les chatbots et le système central.</li>
            </ul>
            <h3>Filtrage et Validation :</h3>
            <ul>
                <li>Vérification des requêtes et filtrage des données pour empêcher la divulgation d'informations sensibles.</li>
                <li>Journalisation des interactions pour traçabilité et audit.</li>
            </ul>

            <h2 id="fiches-dynamiques">6.7. Fiches Dynamiques</h2>
            <h3>Saisie et Accès Sécurisés :</h3>
            <ul>
                <li>Authentification obligatoire pour accéder aux fiches et modifier les données.</li>
                <li>Validation des saisies pour éviter injections de code ou corruption de données.</li>
            </ul>
            <h3>Mode Hors-Ligne et Synchronisation :</h3>
            <ul>
                <li>Stockage local sécurisé (avec chiffrement) en mode offline.</li>
                <li>Synchronisation automatique et journalisation des mises à jour une fois la connexion rétablie.</li>
            </ul>

            <h2 id="app-mobile">6.8. Application Mobile</h2>
            <h3>Développement et Authentification :</h3>
            <ul>
                <li>Développement en React Native suivant les bonnes pratiques de sécurité.</li>
                <li>Authentification renforcée similaire à l'interface web, avec gestion des sessions.</li>
            </ul>
            <h3>Communication Sécurisée :</h3>
            <ul>
                <li>Chiffrement des échanges via HTTPS/TLS.</li>
                <li>Déconnexion automatique après inactivité prolongée.</li>
            </ul>
            <h3>Mises à Jour et Patching :</h3>
            <p>Processus de mise à jour continue pour corriger rapidement toute vulnérabilité identifiée.</p>
        </section>

        <section id="mesures-organisationnelles" class="content-section">
            <h1>7. MESURES ORGANISATIONNELLES, FORMATION ET SENSIBILISATION</h1>
            <ul>
                <li><strong>Sessions de Formation :</strong> Organisation de formations régulières pour tous les utilisateurs (administrateurs, contributeurs, etc.) sur les bonnes pratiques de sécurité, la gestion des mots de passe et la détection des attaques de phishing.</li>
                <li><strong>Procédures Internes Documentées :</strong> Élaboration de guides spécifiques à chaque module (interface d'administration, collecte, notifications, archivage, etc.) détaillant les procédures d'utilisation et de sécurité.</li>
                <li><strong>Campagnes de Sensibilisation :</strong> Diffusion de supports de communication (affiches, newsletters, vidéos) rappelant les consignes de sécurité.</li>
                <li><strong>Audit Interne :</strong> Réalisation d'audits périodiques pour vérifier la conformité aux règles de sécurité et identifier les axes d'amélioration.</li>
            </ul>
        </section>

        <section id="incidents" class="content-section">
            <h1>8. GESTION DES INCIDENTS DE SÉCURITÉ</h1>

            <h2 id="detection">8.1. Détection et Signalement</h2>
            <h3>Surveillance Continue :</h3>
            <ul>
                <li>Utilisation de systèmes IDS/IPS et de solutions de monitoring pour détecter toute activité anormale ou tentative d'intrusion.</li>
                <li>Analyse en temps réel des logs de tous les modules critiques.</li>
            </ul>
            <h3>Procédure de Signalement :</h3>
            <ul>
                <li>Mise en place d'un canal pour permettre aux utilisateurs de signaler immédiatement tout incident suspect.</li>
                <li>Documentation détaillée de chaque incident avec date, heure, nature, et actions prises.</li>
            </ul>

            <h2 id="reponse">8.2. Réponse et Remédiation</h2>
            <h3>Plan de Réponse aux Incidents (PRI) :</h3>
            <ul>
                <li>Définition claire des rôles et responsabilités en cas d'incident.</li>
                <li>Procédures d'isolement, analyse, correction et communication (interne et externe) en cas de compromission.</li>
            </ul>
            <h3>Communication et Analyse Post-Incident :</h3>
            <ul>
                <li>Information rapide des parties prenantes et, si nécessaire, des autorités compétentes.</li>
                <li>Réalisation d'une analyse post-incident pour identifier les failles et ajuster les mesures de sécurité.</li>
            </ul>
        </section>

        <section id="continuite" class="content-section">
            <h1>9. PLAN DE CONTINUITÉ D'ACTIVITÉ ET REPRISE APRÈS SINISTRE (PCA/PRA)</h1>
            <ul>
                <li><strong>Sauvegardes et Rétention :</strong> Sauvegardes régulières des bases de données (MySQL), des documents d'archivage (ArchiPro) et des configurations système. Stockage des sauvegardes sur des serveurs redondants et/ou dans le cloud avec chiffrement.</li>
                <li><strong>Objectifs de Rétablissement :</strong> Définir des objectifs de temps de rétablissement (RTO) et des points de récupération (RPO) pour chaque module critique.</li>
                <li><strong>Environnement de Secours :</strong> Mise en place d'un environnement de secours prêt à être activé en cas de défaillance majeure.</li>
                <li><strong>Tests et Simulations :</strong> Réalisation régulière de tests de continuité et simulations de reprise pour valider l'efficacité du PCA/PRA.</li>
            </ul>
        </section>

        <section id="conformite" class="content-section">
            <h1>10. CONFORMITÉ, AUDITS ET MISE À JOUR DE LA POLITIQUE</h1>
            <ul>
                <li><strong>Conformité aux Normes :</strong> Veiller à la conformité du système avec les normes internationales et aux exigences locales en matière de protection des données (ISO 27001, APDP).</li>
                <li><strong>Audits Externes et Internes :</strong> Organisation d'audits de sécurité réguliers afin d'identifier et corriger les vulnérabilités.</li>
                <li><strong>Révision de la Politique :</strong> Mise à jour annuelle (ou après tout incident majeur) de la présente politique, en intégrant les retours d'expérience et l'évolution technologique.</li>
            </ul>
        </section>

        <section id="gouvernance" class="content-section">
            <h1>11. GOUVERNANCE ET SUIVI</h1>
            <ul>
                <li><strong>Comité de Sécurité :</strong> Création d'un comité réunissant des représentants de l'équipe technique, des administrateurs et de l'équipe de gestion. Réunions régulières pour valider les nouvelles mesures de sécurité et suivre l'évolution des incidents.</li>
                <li><strong>Indicateurs de Performance (KPI) :</strong> Suivi des temps de réponse aux incidents, du nombre de vulnérabilités détectées et corrigées, et de la réussite des mises à jour.</li>
                <li><strong>Archivage de la Documentation :</strong> Conservation de tous les audits, rapports d'incidents et mises à jour pour une traçabilité complète.</li>
            </ul>
        </section>

        <section id="annexes" class="content-section">
            <h1>12. ANNEXES</h1>

            <div class="glossary">
                <h2>Glossaire :</h2>
                <div class="glossary-grid">
                    <div class="glossary-term">
                        <strong>APDP (Autorité de Protection des Données Personnelles)</strong>
                        Organisme chargé de veiller au respect des lois locales sur la protection des données personnelles au Burundi.
                    </div>

                    <div class="glossary-term">
                        <strong>API (Application Programming Interface)</strong>
                        Interface permettant à différents logiciels de communiquer entre eux et d'échanger des données de manière standardisée.
                    </div>

                    <div class="glossary-term">
                        <strong>ArchiPro</strong>
                        Système d'archivage électronique centralisé du MRV Burundi, utilisé pour stocker et sécuriser des documents (rapports, contrats, études).
                    </div>

                    <div class="glossary-term">
                        <strong>Authentification Multi-Facteurs (MFA)</strong>
                        Méthode de vérification de l'identité d'un utilisateur nécessitant au moins deux preuves distinctes (ex. mot de passe + code SMS).
                    </div>

                    <div class="glossary-term">
                        <strong>Chatbots</strong>
                        Outils automatisés simulant une conversation humaine, utilisés pour fournir des informations ou assister les utilisateurs.
                    </div>

                    <div class="glossary-term">
                        <strong>Chiffrement</strong>
                        Processus de conversion des données en un format illisible sans une clé de déchiffrement, afin de protéger leur confidentialité.
                    </div>

                    <div class="glossary-term">
                        <strong>Classification TLP (Traffic Light Protocol)</strong>
                        Système de classification des informations selon leur sensibilité : TLP WHITE (libre diffusion), TLP GREEN (partageable en communauté), TLP AMBER (restreint organisation), TLP RED (destinataires directs).
                    </div>

                    <div class="glossary-term">
                        <strong>Cloud</strong>
                        Stockage et gestion de données sur des serveurs distants accessibles via Internet.
                    </div>

                    <div class="glossary-term">
                        <strong>CSRF (Cross-Site Request Forgery)</strong>
                        Attaque exploitant une session utilisateur authentifiée pour exécuter des actions non autorisées.
                    </div>

                    <div class="glossary-term">
                        <strong>DDoS (Distributed Denial of Service)</strong>
                        Attaque visant à rendre un service indisponible en le submergeant de requêtes.
                    </div>

                    <div class="glossary-term">
                        <strong>Express.js</strong>
                        Framework de développement d'applications web en Node.js.
                    </div>

                    <div class="glossary-term">
                        <strong>HTTPS (HyperText Transfer Protocol Secure)</strong>
                        Protocole de communication sécurisé entre un navigateur et un serveur web, utilisant TLS/SSL.
                    </div>

                    <div class="glossary-term">
                        <strong>IDS/IPS (Intrusion Detection System/Intrusion Prevention System)</strong>
                        Systèmes de détection et de prévention d'intrusions sur un réseau.
                    </div>

                    <div class="glossary-term">
                        <strong>IoT (Internet of Things)</strong>
                        Dispositifs physiques connectés à Internet (capteurs, objets intelligents) collectant et échangeant des données.
                    </div>

                    <div class="glossary-term">
                        <strong>ISO 27001</strong>
                        Norme internationale pour la gestion de la sécurité de l'information.
                    </div>

                    <div class="glossary-term">
                        <strong>KPI (Key Performance Indicator)</strong>
                        Indicateur mesurant l'efficacité d'un processus ou d'une action.
                    </div>

                    <div class="glossary-term">
                        <strong>Logs</strong>
                        Enregistrements chronologiques des activités et événements d'un système.
                    </div>

                    <div class="glossary-term">
                        <strong>MySQL</strong>
                        Système de gestion de bases de données relationnelles open source.
                    </div>

                    <div class="glossary-term">
                        <strong>Phishing</strong>
                        Technique d'hameçonnage visant à tromper un utilisateur pour voler des informations sensibles.
                    </div>

                    <div class="glossary-term">
                        <strong>Plan de Réponse aux Incidents (PRI)</strong>
                        Procédures définies pour identifier, contenir et résoudre un incident de sécurité.
                    </div>

                    <div class="glossary-term">
                        <strong>Prisma</strong>
                        Outil de gestion de bases de données moderne (ORM).
                    </div>

                    <div class="glossary-term">
                        <strong>React.js/React Native</strong>
                        Bibliothèques JavaScript pour développer des interfaces web (React.js) et des applications mobiles (React Native).
                    </div>

                    <div class="glossary-term">
                        <strong>RPO (Recovery Point Objective)</strong>
                        Durée maximale de perte de données acceptable après un sinistre.
                    </div>

                    <div class="glossary-term">
                        <strong>RTO (Recovery Time Objective)</strong>
                        Délai maximal acceptable pour restaurer un service après un incident.
                    </div>

                    <div class="glossary-term">
                        <strong>Sauvegardes</strong>
                        Copies de données permettant une restauration en cas de perte ou de corruption.
                    </div>

                    <div class="glossary-term">
                        <strong>TLS/SSL (Transport Layer Security/Secure Sockets Layer)</strong>
                        Protocoles de chiffrement sécurisant les communications sur Internet.
                    </div>

                    <div class="glossary-term">
                        <strong>XSS (Cross-Site Scripting)</strong>
                        Vulnérabilité permettant d'injecter des scripts malveillants dans des pages web.
                    </div>
                </div>
            </div>
        </section>
    </div>
</body>

</html>