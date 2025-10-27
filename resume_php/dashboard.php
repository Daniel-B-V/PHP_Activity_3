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

// Fetch current user data
$user_id = $_SESSION['user_id'];
$current_user_data = null;

try {
    $sql = "SELECT * FROM resume_users WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $user_id);
    $stmt->execute();
    $current_user_data = $stmt->fetch(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Error fetching user data: " . $e->getMessage();
}

// Default resume data (you can modify this structure)
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

// Get current resume data or use default
if ($current_user_data && isset($current_user_data['resume_data'])) {
    $resume_data = json_decode($current_user_data['resume_data'], true);
} else {
    $resume_data = $default_resume_data;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_resume'])) {
    $updated_resume_data = [
        'full_name' => trim($_POST['full_name']),
        'location' => trim($_POST['location']),
        'phone' => trim($_POST['phone']),
        'email' => trim($_POST['email']),
        'objective' => trim($_POST['objective']),
        'education' => [
            'school' => trim($_POST['education_school']),
            'degree' => trim($_POST['education_degree']),
            'graduation' => trim($_POST['education_graduation']),
            'location' => trim($_POST['education_location'])
        ],
        'skills' => [
            'programming' => array_filter(array_map('trim', explode(',', $_POST['skills_programming']))),
            'web' => array_filter(array_map('trim', explode(',', $_POST['skills_web']))),
            'mobile' => array_filter(array_map('trim', explode(',', $_POST['skills_mobile']))),
            'databases' => array_filter(array_map('trim', explode(',', $_POST['skills_databases']))),
            'tools' => array_filter(array_map('trim', explode(',', $_POST['skills_tools'])))
        ],
        'projects' => [
            [
                'title' => trim($_POST['project_title']),
                'points' => array_filter([
                    trim($_POST['project_point1']),
                    trim($_POST['project_point2']),
                    trim($_POST['project_point3'])
                ]),
                'technologies' => trim($_POST['project_technologies'])
            ]
        ],
        'experience' => array_filter(array_map('trim', explode("\n", $_POST['experience']))),
        'organizations' => array_filter(array_map('trim', explode("\n", $_POST['organizations'])))
    ];

    // Validate required fields
    $required_fields = ['full_name', 'location', 'phone', 'email', 'objective'];
    $is_valid = true;
    
    foreach ($required_fields as $field) {
        if (empty($updated_resume_data[$field])) {
            $is_valid = false;
            break;
        }
    }

    if ($is_valid) {
        try {
            $json_resume_data = json_encode($updated_resume_data);
            
            $update_sql = "UPDATE resume_users SET resume_data = :resume_data WHERE id = :id";
            $update_stmt = $pdo->prepare($update_sql);
            $update_stmt->bindParam(':resume_data', $json_resume_data);
            $update_stmt->bindParam(':id', $user_id);
            
            if ($update_stmt->execute()) {
                $success = "Resume updated successfully!";
                $resume_data = $updated_resume_data;
                
                // Update session email if changed
                if ($_SESSION['email'] !== $updated_resume_data['email']) {
                    $_SESSION['email'] = $updated_resume_data['email'];
                }
            } else {
                $error = "Failed to update resume. Please try again.";
            }
        } catch(PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    } else {
        $error = "Please fill in all required fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Resume - Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }
        .header {
            background: #2c3e50;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            background: #34495e;
        }
        .nav a {
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 5px;
            transition: background 0.3s;
        }
        .nav a:hover {
            background: #4a6278;
        }
        .content {
            padding: 30px;
        }
        .form-section {
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid #e1e1e1;
            border-radius: 10px;
            background: #f9f9f9;
        }
        .form-section h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #3498db;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #555;
        }
        input, textarea, select {
            width: 100%;
            padding: 10px;
            border: 2px solid #e1e1e1;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: #3498db;
        }
        textarea {
            min-height: 80px;
            resize: vertical;
        }
        .btn {
            background: #3498db;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #2980b9;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }
        .skills-hint {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Edit Your Resume</h1>
            <p>Update your professional information</p>
        </div>
        
        <div class="nav">
            <a href="resume.php">View My Resume</a>
            <a href="login_signup.php?logout=true">Logout</a>
        </div>
        
        <div class="content">
            <?php if (isset($success)): ?>
                <div class="success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <!-- Personal Information -->
                <div class="form-section">
                    <h3>Personal Information</h3>
                    <div class="form-group">
                        <label for="full_name">Full Name *</label>
                        <input type="text" id="full_name" name="full_name" 
                               value="<?php echo htmlspecialchars($resume_data['full_name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="location">Location *</label>
                        <input type="text" id="location" name="location" 
                               value="<?php echo htmlspecialchars($resume_data['location']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone *</label>
                        <input type="text" id="phone" name="phone" 
                               value="<?php echo htmlspecialchars($resume_data['phone']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" 
                               value="<?php echo htmlspecialchars($resume_data['email']); ?>" required>
                    </div>
                </div>

                <!-- Career Objective -->
                <div class="form-section">
                    <h3>Career Objective</h3>
                    <div class="form-group">
                        <label for="objective">Objective Statement *</label>
                        <textarea id="objective" name="objective" required><?php echo htmlspecialchars($resume_data['objective']); ?></textarea>
                    </div>
                </div>

                <!-- Education -->
                <div class="form-section">
                    <h3>Education</h3>
                    <div class="form-group">
                        <label for="education_school">School/University *</label>
                        <input type="text" id="education_school" name="education_school" 
                               value="<?php echo htmlspecialchars($resume_data['education']['school']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="education_degree">Degree/Program *</label>
                        <input type="text" id="education_degree" name="education_degree" 
                               value="<?php echo htmlspecialchars($resume_data['education']['degree']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="education_graduation">Graduation Date *</label>
                        <input type="text" id="education_graduation" name="education_graduation" 
                               value="<?php echo htmlspecialchars($resume_data['education']['graduation']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="education_location">Location *</label>
                        <input type="text" id="education_location" name="education_location" 
                               value="<?php echo htmlspecialchars($resume_data['education']['location']); ?>" required>
                    </div>
                </div>

                <!-- Skills -->
                <div class="form-section">
                    <h3>Skills</h3>
                    <div class="form-group">
                        <label for="skills_programming">Programming Languages</label>
                        <input type="text" id="skills_programming" name="skills_programming" 
                               value="<?php echo htmlspecialchars(implode(', ', $resume_data['skills']['programming'])); ?>">
                        <div class="skills-hint">Separate skills with commas</div>
                    </div>
                    <div class="form-group">
                        <label for="skills_web">Web Development</label>
                        <input type="text" id="skills_web" name="skills_web" 
                               value="<?php echo htmlspecialchars(implode(', ', $resume_data['skills']['web'])); ?>">
                        <div class="skills-hint">Separate skills with commas</div>
                    </div>
                    <div class="form-group">
                        <label for="skills_mobile">Mobile Development</label>
                        <input type="text" id="skills_mobile" name="skills_mobile" 
                               value="<?php echo htmlspecialchars(implode(', ', $resume_data['skills']['mobile'])); ?>">
                        <div class="skills-hint">Separate skills with commas</div>
                    </div>
                    <div class="form-group">
                        <label for="skills_databases">Databases</label>
                        <input type="text" id="skills_databases" name="skills_databases" 
                               value="<?php echo htmlspecialchars(implode(', ', $resume_data['skills']['databases'])); ?>">
                        <div class="skills-hint">Separate skills with commas</div>
                    </div>
                    <div class="form-group">
                        <label for="skills_tools">Tools</label>
                        <input type="text" id="skills_tools" name="skills_tools" 
                               value="<?php echo htmlspecialchars(implode(', ', $resume_data['skills']['tools'])); ?>">
                        <div class="skills-hint">Separate skills with commas</div>
                    </div>
                </div>

                <!-- Projects -->
                <div class="form-section">
                    <h3>Projects</h3>
                    <div class="form-group">
                        <label for="project_title">Project Title *</label>
                        <input type="text" id="project_title" name="project_title" 
                               value="<?php echo htmlspecialchars($resume_data['projects'][0]['title']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="project_point1">Project Point 1</label>
                        <input type="text" id="project_point1" name="project_point1" 
                               value="<?php echo htmlspecialchars($resume_data['projects'][0]['points'][0] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="project_point2">Project Point 2</label>
                        <input type="text" id="project_point2" name="project_point2" 
                               value="<?php echo htmlspecialchars($resume_data['projects'][0]['points'][1] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="project_point3">Project Point 3</label>
                        <input type="text" id="project_point3" name="project_point3" 
                               value="<?php echo htmlspecialchars($resume_data['projects'][0]['points'][2] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="project_technologies">Technologies Used</label>
                        <input type="text" id="project_technologies" name="project_technologies" 
                               value="<?php echo htmlspecialchars($resume_data['projects'][0]['technologies']); ?>">
                    </div>
                </div>

                <!-- Experience -->
                <div class="form-section">
                    <h3>Experience</h3>
                    <div class="form-group">
                        <label for="experience">Experience Points</label>
                        <textarea id="experience" name="experience" placeholder="Enter each experience point on a new line"><?php echo htmlspecialchars(implode("\n", $resume_data['experience'])); ?></textarea>
                        <div class="skills-hint">Enter each point on a separate line</div>
                    </div>
                </div>

                <!-- Organizations -->
                <div class="form-section">
                    <h3>Organizations</h3>
                    <div class="form-group">
                        <label for="organizations">Organization Memberships</label>
                        <textarea id="organizations" name="organizations" placeholder="Enter each organization on a new line"><?php echo htmlspecialchars(implode("\n", $resume_data['organizations'])); ?></textarea>
                        <div class="skills-hint">Enter each organization on a separate line</div>
                    </div>
                </div>

                <button type="submit" name="update_resume" class="btn">Update Resume</button>
            </form>
        </div>
    </div>
</body>
</html>