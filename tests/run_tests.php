<?php
/**
 * Simple Test Runner - No PHPUnit required
 * Tests the core Member model functionality
 */

echo "============================================\n";
echo "   English Club Attendance - Test Suite   \n";
echo "============================================\n\n";

$testsPassed = 0;
$testsFailed = 0;

// Use SQLite in-memory database for testing
try {
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ SQLite connection established\n\n";
} catch (PDOException $e) {
    die("✗ Failed to connect to SQLite: " . $e->getMessage() . "\n");
}

// Create test tables
$pdo->exec("
    CREATE TABLE members (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name VARCHAR(100) NOT NULL,
        field VARCHAR(100) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )
");

// Mock Member class for testing
class TestMember {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function getAll() {
        $stmt = $this->pdo->query('SELECT * FROM members ORDER BY name ASC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getById($id) {
        $stmt = $this->pdo->prepare('SELECT * FROM members WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function create($data) {
        $stmt = $this->pdo->prepare('INSERT INTO members (name, field, phone, email) VALUES (?, ?, ?, ?)');
        $stmt->execute([$data['name'], $data['field'], $data['phone'], $data['email']]);
        return $this->pdo->lastInsertId();
    }
    
    public function update($id, $data) {
        $stmt = $this->pdo->prepare('UPDATE members SET name = ?, field = ?, phone = ?, email = ? WHERE id = ?');
        return $stmt->execute([$data['name'], $data['field'], $data['phone'], $data['email'], $id]);
    }
    
    public function delete($id) {
        $stmt = $this->pdo->prepare('DELETE FROM members WHERE id = ?');
        return $stmt->execute([$id]);
    }
    
    public function search($query) {
        $stmt = $this->pdo->prepare('SELECT * FROM members WHERE name LIKE ? OR email LIKE ?');
        $stmt->execute(["%$query%", "%$query%"]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function emailExists($email, $excludeId = null) {
        if ($excludeId) {
            $stmt = $this->pdo->prepare('SELECT id FROM members WHERE email = ? AND id != ?');
            $stmt->execute([$email, $excludeId]);
        } else {
            $stmt = $this->pdo->prepare('SELECT id FROM members WHERE email = ?');
            $stmt->execute([$email]);
        }
        return $stmt->fetch() !== false;
    }
    
    public function count() {
        $stmt = $this->pdo->query('SELECT COUNT(*) as total FROM members');
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
}

$member = new TestMember($pdo);

// Test helper function
function runTest($name, $callback) {
    global $testsPassed, $testsFailed;
    try {
        $callback();
        echo "✓ PASS: $name\n";
        $testsPassed++;
    } catch (Exception $e) {
        echo "✗ FAIL: $name - " . $e->getMessage() . "\n";
        $testsFailed++;
    }
}

function assertEquals($expected, $actual, $message = '') {
    if ($expected !== $actual) {
        throw new Exception($message ?: "Expected '$expected' but got '$actual'");
    }
}

function assertTrue($value, $message = '') {
    if (!$value) {
        throw new Exception($message ?: "Expected true but got false");
    }
}

function assertFalse($value, $message = '') {
    if ($value) {
        throw new Exception($message ?: "Expected false but got true");
    }
}

function assertCount($expected, $array, $message = '') {
    if (count($array) !== $expected) {
        throw new Exception($message ?: "Expected $expected items but got " . count($array));
    }
}

// ===== RUN TESTS =====

echo "--- Member Model Tests ---\n\n";

// Test 1: Create Member
runTest("Create Member", function() use ($member) {
    $id = $member->create([
        'name' => 'John Doe',
        'field' => 'Computer Science',
        'phone' => '+1234567890',
        'email' => 'john.doe@example.com'
    ]);
    assertTrue($id > 0, "ID should be greater than 0");
    
    $result = $member->getById($id);
    assertEquals('John Doe', $result['name']);
    assertEquals('Computer Science', $result['field']);
    assertEquals('+1234567890', $result['phone']);
    assertEquals('john.doe@example.com', $result['email']);
});

// Test 2: Get All Members
runTest("Get All Members", function() use ($member) {
    $member->create(['name' => 'Alice', 'field' => 'Math', 'phone' => '111', 'email' => 'alice@test.com']);
    $member->create(['name' => 'Bob', 'field' => 'Physics', 'phone' => '222', 'email' => 'bob@test.com']);
    
    $results = $member->getAll();
    assertCount(3, $results); // 1 from previous test + 2 new
    assertEquals('Alice', $results[0]['name']);
    assertEquals('Bob', $results[1]['name']);
});

// Test 3: Update Member
runTest("Update Member", function() use ($member) {
    $id = $member->create(['name' => 'Test', 'field' => 'Test', 'phone' => '123', 'email' => 'update@test.com']);
    
    $result = $member->update($id, [
        'name' => 'Updated Name',
        'field' => 'Updated Field',
        'phone' => '999',
        'email' => 'updated@test.com'
    ]);
    assertTrue($result);
    
    $updated = $member->getById($id);
    assertEquals('Updated Name', $updated['name']);
    assertEquals('Updated Field', $updated['field']);
});

// Test 4: Delete Member
runTest("Delete Member", function() use ($member) {
    $id = $member->create(['name' => 'ToDelete', 'field' => 'Test', 'phone' => '123', 'email' => 'del@test.com']);
    
    $result = $member->delete($id);
    assertTrue($result);
    
    $deleted = $member->getById($id);
    assertFalse($deleted);
});

// Test 5: Search Members
runTest("Search Members", function() use ($member) {
    // Use a unique search term
    $member->create(['name' => 'SearchTarget XYZ', 'field' => 'CS', 'phone' => '999', 'email' => 'searchxyz@test.com']);
    $member->create(['name' => 'Other Person', 'field' => 'English', 'phone' => '888', 'email' => 'other@test.com']);
    
    $results = $member->search('SearchTarget');
    assertCount(1, $results);
    assertEquals('SearchTarget XYZ', $results[0]['name']);
});

// Test 6: Email Exists
runTest("Email Exists Check", function() use ($member) {
    $member->create(['name' => 'Existing', 'field' => 'Test', 'phone' => '123', 'email' => 'exists@test.com']);
    
    assertTrue($member->emailExists('exists@test.com'));
    assertFalse($member->emailExists('notexists@test.com'));
});

// Test 7: Count Members
runTest("Count Members", function() use ($member) {
    $count = $member->count();
    assertTrue($count > 0, "Count should be greater than 0");
});

// Test 8: Unique Email Constraint
runTest("Unique Email Constraint", function() use ($member) {
    $member->create(['name' => 'First', 'field' => 'Test', 'phone' => '123', 'email' => 'unique@test.com']);
    
    $thrown = false;
    try {
        $member->create(['name' => 'Second', 'field' => 'Test', 'phone' => '456', 'email' => 'unique@test.com']);
    } catch (PDOException $e) {
        $thrown = true;
    }
    assertTrue($thrown, "Should throw exception for duplicate email");
});

// ===== SUMMARY =====
echo "\n============================================\n";
echo "   Test Results: $testsPassed passed, $testsFailed failed\n";
echo "============================================\n";

exit($testsFailed > 0 ? 1 : 0);

