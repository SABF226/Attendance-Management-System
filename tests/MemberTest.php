<?php
/**
 * Member Model Test Case
 */

use PHPUnit\Framework\TestCase;

class MemberTest extends TestCase
{
    private $pdo;
    private $member;
    
    protected function setUp(): void
    {
        // Use SQLite in-memory database for testing
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create test tables
        $this->pdo->exec("
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
        
        // Create mock Member class that uses our test PDO
        $this->member = new class($this->pdo) {
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
        };
    }
    
    protected function tearDown(): void
    {
        $this->pdo = null;
    }
    
    public function testCreateMember()
    {
        $data = [
            'name' => 'John Doe',
            'field' => 'Computer Science',
            'phone' => '+1234567890',
            'email' => 'john.doe@example.com'
        ];
        
        $id = $this->member->create($data);
        
        $this->assertGreaterThan(0, $id);
        $this->assertIsInt($id);
        
        $member = $this->member->getById($id);
        $this->assertEquals('John Doe', $member['name']);
        $this->assertEquals('Computer Science', $member['field']);
        $this->assertEquals('+1234567890', $member['phone']);
        $this->assertEquals('john.doe@example.com', $member['email']);
    }
    
    public function testGetAllMembers()
    {
        // Insert test data
        $this->member->create(['name' => 'Alice', 'field' => 'Math', 'phone' => '111', 'email' => 'alice@test.com']);
        $this->member->create(['name' => 'Bob', 'field' => 'Physics', 'phone' => '222', 'email' => 'bob@test.com']);
        
        $members = $this->member->getAll();
        
        $this->assertCount(2, $members);
        $this->assertEquals('Alice', $members[0]['name']);
        $this->assertEquals('Bob', $members[1]['name']);
    }
    
    public function testUpdateMember()
    {
        $id = $this->member->create(['name' => 'Test', 'field' => 'Test', 'phone' => '123', 'email' => 'test@test.com']);
        
        $result = $this->member->update($id, [
            'name' => 'Updated Name',
            'field' => 'Updated Field',
            'phone' => '999',
            'email' => 'updated@test.com'
        ]);
        
        $this->assertTrue($result);
        
        $member = $this->member->getById($id);
        $this->assertEquals('Updated Name', $member['name']);
        $this->assertEquals('Updated Field', $member['field']);
    }
    
    public function testDeleteMember()
    {
        $id = $this->member->create(['name' => 'ToDelete', 'field' => 'Test', 'phone' => '123', 'email' => 'del@test.com']);
        
        $result = $this->member->delete($id);
        $this->assertTrue($result);
        
        $member = $this->member->getById($id);
        $this->assertFalse($member);
    }
    
    public function testSearchMembers()
    {
        $this->member->create(['name' => 'John Smith', 'field' => 'CS', 'phone' => '111', 'email' => 'john@test.com']);
        $this->member->create(['name' => 'Jane Doe', 'field' => 'English', 'phone' => '222', 'email' => 'jane@test.com']);
        
        $results = $this->member->search('John');
        
        $this->assertCount(1, $results);
        $this->assertEquals('John Smith', $results[0]['name']);
    }
    
    public function testEmailExists()
    {
        $this->member->create(['name' => 'Existing', 'field' => 'Test', 'phone' => '123', 'email' => 'exists@test.com']);
        
        $this->assertTrue($this->member->emailExists('exists@test.com'));
        $this->assertFalse($this->member->emailExists('notexists@test.com'));
    }
    
    public function testCountMembers()
    {
        $this->assertEquals(0, $this->member->count());
        
        $this->member->create(['name' => 'One', 'field' => 'Test', 'phone' => '1', 'email' => 'one@test.com']);
        $this->member->create(['name' => 'Two', 'field' => 'Test', 'phone' => '2', 'email' => 'two@test.com']);
        
        $this->assertEquals(2, $this->member->count());
    }
    
    public function testUniqueEmailConstraint()
    {
        $this->member->create(['name' => 'First', 'field' => 'Test', 'phone' => '123', 'email' => 'unique@test.com']);
        
        $this->expectException(PDOException::class);
        $this->member->create(['name' => 'Second', 'field' => 'Test', 'phone' => '456', 'email' => 'unique@test.com']);
    }
}

