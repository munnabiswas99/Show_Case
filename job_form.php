<?php
session_start();
$conn = new mysqli("localhost", "root", "", "showcase");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

if (!isset($_SESSION['user_id'])) {
    echo "<div class='text-red-500 text-center'>❌ Login required</div>";
    exit;
}
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $job_title = $_POST['job_title'];
    $company_name = $_POST['company_name'];
    $location = $_POST['location'];
    $age_limit = $_POST['age_limit'];
    $salary = $_POST['salary'];
    $experience = $_POST['experience'];
    $published = $_POST['published'];
    $deadline = $_POST['deadline'];
    $description = $_POST['description'];
    $education = $_POST['education'];
    $requirements = $_POST['requirements'];
    $responsibilities = $_POST['responsibilities'];
    $skills = $_POST['skills'];
    $employment_status = $_POST['employment_status'];
    $apply_procedure = $_POST['apply_procedure'];

    $stmt = $conn->prepare("INSERT INTO jobs (user_id, job_title, company_name, location, age_limit, salary, experience, published, deadline, description, education, requirements, responsibilities, skills, employment_status, apply_procedure) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssssssssssssss", $user_id, $job_title, $company_name, $location, $age_limit, $salary, $experience, $published, $deadline, $description, $education, $requirements, $responsibilities, $skills, $employment_status, $apply_procedure);

    if ($stmt->execute()) {
        echo "<script>
            alert('✅ Job posted successfully!');
            setTimeout(() => { window.location.href = 'job.php'; }, 2000);
        </script>";
    } else {
        echo "<div class='text-red-500 text-center'>❌ Error: " . $stmt->error . "</div>";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Post Job</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow">
        <h2 class="text-2xl font-bold mb-4 text-center">Post a Job</h2>
        <form method="POST" class="space-y-4">
            <input type="text" name="job_title" placeholder="Job Title" required class="w-full border px-4 py-2 rounded" />
            <input type="text" name="company_name" placeholder="Company Name" required class="w-full border px-4 py-2 rounded" />
            <input type="text" name="location" placeholder="Job Location" required class="w-full border px-4 py-2 rounded" />
            <input type="text" name="age_limit" placeholder="Age Limit" class="w-full border px-4 py-2 rounded" />
            <input type="text" name="salary" placeholder="Salary" class="w-full border px-4 py-2 rounded" />
            <input type="text" name="experience" placeholder="Experience" class="w-full border px-4 py-2 rounded" />
            <div class="flex gap-4">
                <input type="date" name="published" class="w-full border px-4 py-2 rounded" />
                <input type="date" name="deadline" class="w-full border px-4 py-2 rounded" />
            </div>
            <textarea name="description" placeholder="Job Description" class="w-full border px-4 py-2 rounded"></textarea>
            <textarea name="education" placeholder="Education Requirements" class="w-full border px-4 py-2 rounded"></textarea>
            <textarea name="requirements" placeholder="Additional Requirements" class="w-full border px-4 py-2 rounded"></textarea>
            <textarea name="responsibilities" placeholder="Job Responsibilities" class="w-full border px-4 py-2 rounded"></textarea>
            <textarea name="skills" placeholder="Skills" class="w-full border px-4 py-2 rounded"></textarea>
            <input type="text" name="employment_status" placeholder="Employment Status (e.g. Full Time)" class="w-full border px-4 py-2 rounded" />
            <textarea name="apply_procedure" placeholder="Apply Procedure" class="w-full border px-4 py-2 rounded"></textarea>

            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Post Job</button>
        </form>
    </div>
</body>

</html>