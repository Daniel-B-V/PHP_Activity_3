<?php
session_start();
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

// Get user ID from URL parameter
$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($user_id <= 0) {
    die("Invalid resume ID");
}

// Fetch user data
try {
    $sql = "SELECT username, resume_data FROM resume_users WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $user_id);
    $stmt->execute();
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user_data || empty($user_data['resume_data'])) {
        die("Resume not found or not available");
    }
    
    $resume_data = json_decode($user_data['resume_data'], true);
    $username = $user_data['username'];
    
} catch(PDOException $e) {
    die("Error fetching resume: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($resume_data['full_name']); ?> - Resume</title>
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
        
        .public-notice {
            text-align: center;
            margin-bottom: 20px;
            padding: 10px;
            background: #e8f4fc;
            border-radius: 5px;
            color: #2c3e50;
        }
        
        @media (max-width: 600px) {
            .skills-grid {
                grid-template-columns: 1fr;
            }
            .contact-info {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="public-notice">
        Public Resume - <strong><?php echo htmlspecialchars($resume_data['full_name']); ?></strong>
    </div>

    <div class="resume">
        <div class="header">
            <div class="name"><?php echo htmlspecialchars($resume_data['full_name']); ?></div>
            <div class="contact-info">
                <div class="contact-item"><?php echo htmlspecialchars($resume_data['location']); ?></div>
                <div class="contact-item"><?php echo htmlspecialchars($resume_data['phone']); ?></div>
                <div class="contact-item">
                    <a class="email" href="mailto:<?php echo htmlspecialchars($resume_data['email']); ?>">
                        <?php echo htmlspecialchars($resume_data['email']); ?>
                    </a>
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Career Objective</div>
            <p><?php echo htmlspecialchars($resume_data['objective']); ?></p>
        </div>

        <div class="section">
            <div class="section-title">Technical Skills</div>
            <div class="skills-grid">
                <?php if (!empty($resume_data['skills']['programming'])): ?>
                <div class="skill-category">
                    <div class="skill-category-title">Programming Languages</div>
                    <div class="skill-list">
                        <?php foreach ($resume_data['skills']['programming'] as $skill): ?>
                            <div class="skill"><?php echo htmlspecialchars($skill); ?></div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($resume_data['skills']['web'])): ?>
                <div class="skill-category">
                    <div class="skill-category-title">Web Development</div>
                    <div class="skill-list">
                        <?php foreach ($resume_data['skills']['web'] as $skill): ?>
                            <div class="skill"><?php echo htmlspecialchars($skill); ?></div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($resume_data['skills']['mobile'])): ?>
                <div class="skill-category">
                    <div class="skill-category-title">Mobile Development</div>
                    <div class="skill-list">
                        <?php foreach ($resume_data['skills']['mobile'] as $skill): ?>
                            <div class="skill"><?php echo htmlspecialchars($skill); ?></div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($resume_data['skills']['databases'])): ?>
                <div class="skill-category">
                    <div class="skill-category-title">Databases</div>
                    <div class="skill-list">
                        <?php foreach ($resume_data['skills']['databases'] as $skill): ?>
                            <div class="skill"><?php echo htmlspecialchars($skill); ?></div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($resume_data['skills']['tools'])): ?>
                <div class="skill-category">
                    <div class="skill-category-title">Tools</div>
                    <div class="skill-list">
                        <?php foreach ($resume_data['skills']['tools'] as $skill): ?>
                            <div class="skill"><?php echo htmlspecialchars($skill); ?></div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Projects & Experience</div>
            <?php if (!empty($resume_data['projects'])): ?>
                <?php foreach ($resume_data['projects'] as $project): ?>
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
            
            <?php if (!empty($resume_data['experience'])): ?>
                <div class="project">
                    <div class="project-title">Additional Practical Experience</div>
                    <ul>
                        <?php foreach ($resume_data['experience'] as $exp): ?>
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
                <div class="school"><?php echo htmlspecialchars($resume_data['education']['school']); ?></div>
                <div><?php echo htmlspecialchars($resume_data['education']['degree']); ?></div>
                <div><?php echo htmlspecialchars($resume_data['education']['graduation']); ?></div>
                <div><?php echo htmlspecialchars($resume_data['education']['location']); ?></div>
            </div>
        </div>

        <?php if (!empty($resume_data['organizations'])): ?>
        <div class="section">
            <div class="section-title">Organizations</div>
            <ul>
                <?php foreach ($resume_data['organizations'] as $org): ?>
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