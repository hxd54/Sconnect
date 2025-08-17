<?php
/**
 * Python Installation Status Checker
 * Checks if Python is installed and accessible
 */

header('Content-Type: application/json');

function checkPythonInstallation() {
    $result = [
        'python_installed' => false,
        'python_version' => null,
        'pip_version' => null,
        'python_path' => null,
        'error' => null
    ];
    
    try {
        // Check Python version
        $python_output = shell_exec('python --version 2>&1');
        
        if ($python_output && strpos($python_output, 'Python') !== false) {
            $result['python_installed'] = true;
            $result['python_version'] = trim($python_output);
            
            // Check pip version
            $pip_output = shell_exec('pip --version 2>&1');
            if ($pip_output && strpos($pip_output, 'pip') !== false) {
                $result['pip_version'] = trim(explode("\n", $pip_output)[0]);
            }
            
            // Get Python path
            $python_path = shell_exec('where python 2>&1');
            if ($python_path) {
                $result['python_path'] = trim($python_path);
            }
        } else {
            // Try python3 command (Linux/Mac style)
            $python3_output = shell_exec('python3 --version 2>&1');
            if ($python3_output && strpos($python3_output, 'Python') !== false) {
                $result['python_installed'] = true;
                $result['python_version'] = trim($python3_output);
                
                $pip3_output = shell_exec('pip3 --version 2>&1');
                if ($pip3_output && strpos($pip3_output, 'pip') !== false) {
                    $result['pip_version'] = trim(explode("\n", $pip3_output)[0]);
                }
            }
        }
        
        if (!$result['python_installed']) {
            $result['error'] = 'Python is not installed or not in PATH';
        }
        
    } catch (Exception $e) {
        $result['error'] = 'Error checking Python: ' . $e->getMessage();
    }
    
    return $result;
}

function checkPythonDependencies() {
    $dependencies = [
        'fastapi',
        'uvicorn',
        'pandas',
        'requests'
    ];
    
    $installed = [];
    $missing = [];
    
    foreach ($dependencies as $dep) {
        $output = shell_exec("python -c \"import $dep; print('$dep installed')\" 2>&1");
        if (strpos($output, 'installed') !== false) {
            $installed[] = $dep;
        } else {
            $missing[] = $dep;
        }
    }
    
    return [
        'installed' => $installed,
        'missing' => $missing,
        'all_installed' => empty($missing)
    ];
}

$python_status = checkPythonInstallation();

if ($python_status['python_installed']) {
    $dependencies = checkPythonDependencies();
    $python_status['dependencies'] = $dependencies;
}

echo json_encode($python_status);
?>
