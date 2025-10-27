<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login_signup.php");
    exit();
}

$host = 'localhost';
$port = '5432';
$dbname = 'resume_db';  
$user = 'postgres';      
$password = 'staydead09';

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Fetch user's resume data from database
$user_id = $_SESSION['user_id'];
$resume_data = null;

try {
    $sql = "SELECT username, resume_data FROM resume_users WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $user_id);
    $stmt->execute();
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user_data && !empty($user_data['resume_data'])) {
        $resume_data = json_decode($user_data['resume_data'], true);
    }
} catch(PDOException $e) {
    // Continue with default data if there's an error
}

// Default resume data (fallback if no data in database)
$default_resume_data = [
    'full_name' => 'DANIEL B. VILLANUEVA',
    'location' => 'Bagong Pook, Rosario, Batangas',
    'phone' => '0927-164-6563',
    'email' => 'danielbvillanueva12@gmail.com',
    'objective' => 'Motivated Computer Science student seeking a remote opportunity to develop practical skills in web and mobile development, data handling, and IT-related tasks. While I have no professional work experience yet, I am eager to learn, detail-oriented, and adaptable.',
    'education' => [
        'school' => 'Batangas State University',
        'degree' => 'Bachelor of Science in Computer Science',
        'graduation' => 'Expected Graduation: June 2027',
        'location' => 'Batangas, Philippines'
    ],
    'skills' => [
        'programming' => ['Python', 'JavaScript', 'C#'],
        'web' => ['HTML', 'CSS', 'React'],
        'mobile' => ['Flutter'],
        'databases' => ['MySQL', 'PostgreSQL', 'Firebase'],
        'tools' => ['Git', 'GitHub', 'VS Code', 'Figma']
    ],
    'projects' => [
        [
            'title' => 'Crop Monitoring Website (School Project)',
            'points' => [
                'Assisted in gathering and organizing sample agricultural data.',
                'Developed a simple website for crop tracking using HTML, CSS, and JavaScript.',
                'Contributed to documentation, data presentation, and layout design.'
            ],
            'technologies' => 'HTML, CSS, JavaScript'
        ]
    ],
    'experience' => [
        'Collaborated on academic projects involving data collection, cleaning, and organization.',
        'Participated in web development projects using HTML, CSS, and JavaScript.',
        'Completed online courses in Python programming and web development.'
    ],
    'organizations' => [
        'Junior Philippine Computer Society (JPCS) – Member',
        'Association of Committed Computer Science Students (ACCESS) – Member'
    ]
];

// Use database data if available, otherwise use default
if ($resume_data) {
    $name = $resume_data['full_name'];
    $location = $resume_data['location'];
    $phone = $resume_data['phone'];
    $email = $resume_data['email'];
    $objective = $resume_data['objective'];
    $education = $resume_data['education'];
    $skills = $resume_data['skills'];
    $projects = $resume_data['projects'];
    $experience = $resume_data['experience'];
    $organizations = $resume_data['organizations'];
} else {
    $name = $default_resume_data['full_name'];
    $location = $default_resume_data['location'];
    $phone = $default_resume_data['phone'];
    $email = $default_resume_data['email'];
    $objective = $default_resume_data['objective'];
    $education = $default_resume_data['education'];
    $skills = $default_resume_data['skills'];
    $projects = $default_resume_data['projects'];
    $experience = $default_resume_data['experience'];
    $organizations = $default_resume_data['organizations'];
}

// Generate public URL for sharing
$public_url = "public_resume.php?id=" . $user_id;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($name); ?> - Resume</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        body {
            background-color: #f5f5f5;
            color: #333;
            line-height: 1.6;
            padding: 20px;
            position: relative;
        }
        .resume {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 2px solid #3498db;
        }
        .name {
            font-size: 28px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        .contact-info {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 10px;
        }
        .contact-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .email {
            color: #2980b9;
            text-decoration: none;
        }
        .email:hover {
            text-decoration: underline;
        }
        .section {
            margin-bottom: 25px;
        }
        .section-title {
            font-size: 20px;
            color: #2c3e50;
            padding-bottom: 5px;
            border-bottom: 1px solid #ddd;
            margin-bottom: 15px;
        }
        
        .skills-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        .skill-category {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .skill-category-title {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 12px;
            padding-bottom: 5px;
            border-bottom: 1px dashed #ddd;
        }
        .skill-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .skill {
            background: #e8f4fc;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
            color: #2c3e50;
        }
        
        .education-item {
            margin-bottom: 15px;
        }
        .school {
            font-weight: bold;
            color: #2c3e50;
        }
        .project {
            margin-bottom: 20px;
        }
        .project-title {
            font-weight: bold;
            color: #2c3e50;
        }
        .project-tech {
            font-style: italic;
            color: #7f8c8d;
            margin: 5px 0;
        }
        ul {
            padding-left: 20px;
        }
        li {
            margin-bottom: 8px;
        }
        
        .action-container {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .action-btn {
            display: inline-block;
            width: 140px;
            padding: 10px 15px;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            font-size: 14px;
            transition: background-color 0.3s;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        .edit-btn {
            background-color: #3498db;
        }
        
        .edit-btn:hover {
            background-color: #2980b9;
        }
        
        .public-btn {
            background-color: #27ae60;
        }
        
        .public-btn:hover {
            background-color: #219652;
        }
        
        .logout-btn {
            background-color: #e74c3c;
        }
        
        .logout-btn:hover {
            background-color: #c0392b;
        }
        
        .public-url {
            background: #e8f4fc;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .public-url a {
            color: #2980b9;
            text-decoration: none;
            font-weight: bold;
        }
        
        .public-url a:hover {
            text-decoration: underline;
        }
        
        @media print {
            body {
                background: white;
                padding: 0;
            }
            .resume {
                box-shadow: none;
                padding: 0;
            }
            .action-container {
                display: none;
            }
            .public-url {
                display: none;
            }
        }
        @media (max-width: 600px) {
            .skills-grid {
                grid-template-columns: 1fr;
            }
            .contact-info {
                flex-direction: column;
                gap: 10px;
            }
            .action-container {
                position: relative;
                top: 0;
                left: 0;
                margin-bottom: 20px;
                flex-direction: row;
                justify-content: center;
            }
            .action-btn {
                width: 120px;
            }
        }
    </style>
</head>
<body>
    <div class="action-container">
        <a href="dashboard.php" class="action-btn edit-btn">Edit Resume</a>
        <a href="<?php echo $public_url; ?>" class="action-btn public-btn" target="_blank">Public View</a>
        <a href="login_signup.php?logout=true" class="action-btn logout-btn">Logout</a>
    </div>

    <div class="resume">
        <div class="public-url">
            <strong>Public URL:</strong> 
            <a href="<?php echo $public_url; ?>" target="_blank"><?php echo $public_url; ?></a>
            <br><small>Share this link for others to view your resume</small>
        </div>

        <div class="header">
            <div class="name"><?php echo htmlspecialchars($name); ?></div>
            <div class="contact-info">
                <div class="contact-item"><?php echo htmlspecialchars($location); ?></div>
                <div class="contact-item"><?php echo htmlspecialchars($phone); ?></div>
                <div class="contact-item">
                    <a class="email" href="mailto:<?php echo htmlspecialchars($email); ?>"><?php echo htmlspecialchars($email); ?></a>
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Career Objective</div>
            <p><?php echo htmlspecialchars($objective); ?></p>
        </div>

        <div class="section">
            <div class="section-title">Technical Skills</div>
            <div class="skills-grid">
                <?php if (!empty($skills['programming'])): ?>
                <div class="skill-category">
                    <div class="skill-category-title">Programming Languages</div>
                    <div class="skill-list">
                        <?php foreach ($skills['programming'] as $skill): ?>
                            <div class="skill"><?php echo htmlspecialchars($skill); ?></div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($skills['web'])): ?>
                <div class="skill-category">
                    <div class="skill-category-title">Web Development</div>
                    <div class="skill-list">
                        <?php foreach ($skills['web'] as $skill): ?>
                            <div class="skill"><?php echo htmlspecialchars($skill); ?></div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($skills['mobile'])): ?>
                <div class="skill-category">
                    <div class="skill-category-title">Mobile Development</div>
                    <div class="skill-list">
                        <?php foreach ($skills['mobile'] as $skill): ?>
                            <div class="skill"><?php echo htmlspecialchars($skill); ?></div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($skills['databases'])): ?>
                <div class="skill-category">
                    <div class="skill-category-title">Databases</div>
                    <div class="skill-list">
                        <?php foreach ($skills['databases'] as $skill): ?>
                            <div class="skill"><?php echo htmlspecialchars($skill); ?></div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($skills['tools'])): ?>
                <div class="skill-category">
                    <div class="skill-category-title">Tools</div>
                    <div class="skill-list">
                        <?php foreach ($skills['tools'] as $skill): ?>
                            <div class="skill"><?php echo htmlspecialchars($skill); ?></div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Projects & Experience</div>
            <?php if (!empty($projects)): ?>
                <?php foreach ($projects as $project): ?>
                    <div class="project">
                        <div class="project-title"><?php echo htmlspecialchars($project['title']); ?></div>
                        <?php if (!empty($project['technologies'])): ?>
                            <div class="project-tech">Technologies: <?php echo htmlspecialchars($project['technologies']); ?></div>
                        <?php endif; ?>
                        <?php if (!empty($project['points'])): ?>
                            <ul>
                                <?php foreach ($project['points'] as $point): ?>
                                    <?php if (!empty(trim($point))): ?>
                                        <li><?php echo htmlspecialchars($point); ?></li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <?php if (!empty($experience)): ?>
                <div class="project">
                    <div class="project-title">Additional Practical Experience</div>
                    <ul>
                        <?php foreach ($experience as $exp): ?>
                            <?php if (!empty(trim($exp))): ?>
                                <li><?php echo htmlspecialchars($exp); ?></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>

        <div class="section">
            <div class="section-title">Education</div>
            <div class="education-item">
                <div class="school"><?php echo htmlspecialchars($education['school']); ?></div>
                <div><?php echo htmlspecialchars($education['degree']); ?></div>
                <div><?php echo htmlspecialchars($education['graduation']); ?></div>
                <div><?php echo htmlspecialchars($education['location']); ?></div>
            </div>
        </div>

        <?php if (!empty($organizations)): ?>
        <div class="section">
            <div class="section-title">Organizations</div>
            <ul>
                <?php foreach ($organizations as $org): ?>
                    <?php if (!empty(trim($org))): ?>
                        <li><?php echo htmlspecialchars($org); ?></li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>