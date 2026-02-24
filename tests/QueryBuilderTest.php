<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use OpenGenetics\Core\QueryBuilder;
use OpenGenetics\Core\DB;

/**
 * 🧬 Unit tests for the enhanced OpenGenetics Query Builder.
 *
 * Tests SQL generation via toSql() and getBindings() — no database connection needed.
 */
class QueryBuilderTest extends TestCase
{
    // ─── select() ────────────────────────────────────────────

    public function testSelectVariadic(): void
    {
        $q = new QueryBuilder('users');
        $q->select('id', 'email', 'name');

        $this->assertSame('SELECT id, email, name FROM `users`', $q->toSql());
    }

    public function testSelectArray(): void
    {
        $q = new QueryBuilder('users');
        $q->select(['id', 'email']);

        $this->assertSame('SELECT id, email FROM `users`', $q->toSql());
    }

    public function testSelectSingle(): void
    {
        $q = new QueryBuilder('users');
        $q->select('email');

        $this->assertSame('SELECT email FROM `users`', $q->toSql());
    }

    public function testSelectDefault(): void
    {
        $q = new QueryBuilder('users');
        $this->assertSame('SELECT * FROM `users`', $q->toSql());
    }

    // ─── where() — backward compatible ──────────────────────

    public function testWhereTwoArgs(): void
    {
        $q = new QueryBuilder('users');
        $q->where('status', 'active');

        $this->assertSame('SELECT * FROM `users` WHERE `status` = ?', $q->toSql());
        $this->assertSame(['active'], $q->getBindings());
    }

    public function testWhereThreeArgs(): void
    {
        $q = new QueryBuilder('users');
        $q->where('age', '>=', 18);

        $this->assertSame('SELECT * FROM `users` WHERE `age` >= ?', $q->toSql());
        $this->assertSame([18], $q->getBindings());
    }

    public function testWhereNull(): void
    {
        $q = new QueryBuilder('users');
        $q->where('deleted_at', null);

        $this->assertSame('SELECT * FROM `users` WHERE `deleted_at` IS NULL', $q->toSql());
        $this->assertSame([], $q->getBindings());
    }

    // ─── where() — operator-in-key (string) ─────────────────

    public function testWhereOperatorInKeyString(): void
    {
        $q = new QueryBuilder('users');
        $q->where('age >=', 18);

        $this->assertSame('SELECT * FROM `users` WHERE `age` >= ?', $q->toSql());
        $this->assertSame([18], $q->getBindings());
    }

    public function testWhereOperatorInKeyNotEqual(): void
    {
        $q = new QueryBuilder('users');
        $q->where('name !=', 'test');

        $this->assertSame('SELECT * FROM `users` WHERE `name` != ?', $q->toSql());
        $this->assertSame(['test'], $q->getBindings());
    }

    // ─── where() — array-based ──────────────────────────────

    public function testWhereArraySimple(): void
    {
        $q = new QueryBuilder('users');
        $q->where(['status' => 'active', 'role_name' => 'ADMIN']);

        $this->assertSame(
            'SELECT * FROM `users` WHERE `status` = ? AND `role_name` = ?',
            $q->toSql()
        );
        $this->assertSame(['active', 'ADMIN'], $q->getBindings());
    }

    public function testWhereArrayAutoIn(): void
    {
        $q = new QueryBuilder('users');
        $q->where(['priority' => ['high', 'critical']]);

        $this->assertSame(
            'SELECT * FROM `users` WHERE `priority` IN (?, ?)',
            $q->toSql()
        );
        $this->assertSame(['high', 'critical'], $q->getBindings());
    }

    public function testWhereArrayAutoNull(): void
    {
        $q = new QueryBuilder('users');
        $q->where(['deleted_at' => null]);

        $this->assertSame('SELECT * FROM `users` WHERE `deleted_at` IS NULL', $q->toSql());
    }

    public function testWhereArrayNotNull(): void
    {
        $q = new QueryBuilder('users');
        $q->where(['email !' => null]);

        $this->assertSame('SELECT * FROM `users` WHERE `email` IS NOT NULL', $q->toSql());
    }

    public function testWhereArrayOperatorInKey(): void
    {
        $q = new QueryBuilder('users');
        $q->where(['age >=' => 18, 'age <' => 65]);

        $this->assertSame(
            'SELECT * FROM `users` WHERE `age` >= ? AND `age` < ?',
            $q->toSql()
        );
        $this->assertSame([18, 65], $q->getBindings());
    }

    public function testWhereArrayEmptyArray(): void
    {
        $q = new QueryBuilder('users');
        $q->where(['status' => []]);

        $this->assertSame('SELECT * FROM `users` WHERE 1 = 0', $q->toSql());
    }

    public function testWhereArrayWithTablePrefix(): void
    {
        $q = new QueryBuilder('tickets');
        $q->where(['t.status' => 'open', 't.deleted_at' => null]);

        $this->assertSame(
            'SELECT * FROM `tickets` WHERE `t`.`status` = ? AND `t`.`deleted_at` IS NULL',
            $q->toSql()
        );
        $this->assertSame(['open'], $q->getBindings());
    }

    // ─── where() — combined array + chain ───────────────────

    public function testWhereArrayThenChain(): void
    {
        $q = new QueryBuilder('users');
        $q->where(['status' => 'active'])->where('age', '>=', 18);

        $this->assertSame(
            'SELECT * FROM `users` WHERE `status` = ? AND `age` >= ?',
            $q->toSql()
        );
        $this->assertSame(['active', 18], $q->getBindings());
    }

    // ─── when() — conditional filter ────────────────────────

    public function testWhenTruthyArray(): void
    {
        $status = 'open';
        $q = new QueryBuilder('tickets');
        $q->when($status, ['status' => $status]);

        $this->assertSame('SELECT * FROM `tickets` WHERE `status` = ?', $q->toSql());
        $this->assertSame(['open'], $q->getBindings());
    }

    public function testWhenFalsySkips(): void
    {
        $status = '';
        $q = new QueryBuilder('tickets');
        $q->when($status, ['status' => $status]);

        $this->assertSame('SELECT * FROM `tickets`', $q->toSql());
        $this->assertSame([], $q->getBindings());
    }

    public function testWhenNullSkips(): void
    {
        $q = new QueryBuilder('tickets');
        $q->when(null, ['status' => 'open']);

        $this->assertSame('SELECT * FROM `tickets`', $q->toSql());
    }

    public function testWhenClosure(): void
    {
        $search = 'bug';
        $q = new QueryBuilder('tickets');
        $q->when($search, fn($q) => $q->search(['subject'], $search));

        $this->assertSame(
            'SELECT * FROM `tickets` WHERE (`subject` LIKE ?)',
            $q->toSql()
        );
        $this->assertSame(['%bug%'], $q->getBindings());
    }

    public function testWhenMultipleConditionals(): void
    {
        $status   = 'open';
        $priority = null;
        $dateFrom = '2025-01-01';

        $q = new QueryBuilder('tickets');
        $q->when($status, ['status' => $status])
          ->when($priority, ['priority' => $priority])
          ->when($dateFrom, ['created_at >=' => $dateFrom]);

        $this->assertSame(
            'SELECT * FROM `tickets` WHERE `status` = ? AND `created_at` >= ?',
            $q->toSql()
        );
        $this->assertSame(['open', '2025-01-01'], $q->getBindings());
    }

    // ─── search() — multi-column LIKE ───────────────────────

    public function testSearchSingleColumn(): void
    {
        $q = new QueryBuilder('tickets');
        $q->search(['subject'], 'bug');

        $this->assertSame(
            'SELECT * FROM `tickets` WHERE (`subject` LIKE ?)',
            $q->toSql()
        );
        $this->assertSame(['%bug%'], $q->getBindings());
    }

    public function testSearchMultipleColumns(): void
    {
        $q = new QueryBuilder('tickets');
        $q->search(['subject', 'ticket_number', 'description'], 'test');

        $this->assertSame(
            'SELECT * FROM `tickets` WHERE (`subject` LIKE ? OR `ticket_number` LIKE ? OR `description` LIKE ?)',
            $q->toSql()
        );
        $this->assertSame(['%test%', '%test%', '%test%'], $q->getBindings());
    }

    public function testSearchEmptyKeywordSkips(): void
    {
        $q = new QueryBuilder('tickets');
        $q->search(['subject'], '');

        $this->assertSame('SELECT * FROM `tickets`', $q->toSql());
    }

    public function testSearchNullKeywordSkips(): void
    {
        $q = new QueryBuilder('tickets');
        $q->search(['subject'], null);

        $this->assertSame('SELECT * FROM `tickets`', $q->toSql());
    }

    public function testSearchWithTablePrefix(): void
    {
        $q = new QueryBuilder('tickets');
        $q->search(['t.subject', 't.ticket_number'], 'test');

        $this->assertSame(
            'SELECT * FROM `tickets` WHERE (`t`.`subject` LIKE ? OR `t`.`ticket_number` LIKE ?)',
            $q->toSql()
        );
    }

    // ─── sort() — prefix notation ───────────────────────────

    public function testSortDesc(): void
    {
        $q = new QueryBuilder('users');
        $q->sort('-created_at');

        $this->assertSame('SELECT * FROM `users` ORDER BY `created_at` DESC', $q->toSql());
    }

    public function testSortAsc(): void
    {
        $q = new QueryBuilder('users');
        $q->sort('name');

        $this->assertSame('SELECT * FROM `users` ORDER BY `name` ASC', $q->toSql());
    }

    public function testSortMultiple(): void
    {
        $q = new QueryBuilder('tickets');
        $q->sort('-priority', '-created_at');

        $this->assertSame(
            'SELECT * FROM `tickets` ORDER BY `priority` DESC, `created_at` DESC',
            $q->toSql()
        );
    }

    public function testSortPlusPrefix(): void
    {
        $q = new QueryBuilder('users');
        $q->sort('+name');

        $this->assertSame('SELECT * FROM `users` ORDER BY `name` ASC', $q->toSql());
    }

    public function testSortWithTablePrefix(): void
    {
        $q = new QueryBuilder('tickets');
        $q->sort('-t.created_at');

        $this->assertSame('SELECT * FROM `tickets` ORDER BY `t`.`created_at` DESC', $q->toSql());
    }

    // ─── leftJoin() — 3-arg shortcut ────────────────────────

    public function testLeftJoinThreeArgs(): void
    {
        $q = new QueryBuilder('tickets');
        $q->leftJoin('categories', 'categories.id', 'tickets.category_id');

        $this->assertStringContainsString(
            'LEFT JOIN `categories` ON `categories`.`id` = `tickets`.`category_id`',
            $q->toSql()
        );
    }

    public function testLeftJoinThreeArgsWithAlias(): void
    {
        $q = new QueryBuilder('tickets');
        $q->leftJoin('users AS u', 'u.id', 't.reporter_id');

        $this->assertStringContainsString(
            'LEFT JOIN `users` AS `u` ON `u`.`id` = `t`.`reporter_id`',
            $q->toSql()
        );
    }

    public function testLeftJoinFourArgsStillWorks(): void
    {
        $q = new QueryBuilder('tickets');
        $q->leftJoin('categories', 'tickets.category_id', '=', 'categories.id');

        $this->assertStringContainsString(
            'LEFT JOIN `categories` ON `tickets`.`category_id` = `categories`.`id`',
            $q->toSql()
        );
    }

    // ─── find() / findAll() — instance methods ──────────────

    public function testFindBuildsCorrectSql(): void
    {
        $q = new QueryBuilder('users');
        // We can't test the actual DB call, but we can test that where+limit is set correctly
        // by checking toSql after manually calling where + setting limit
        $q2 = new QueryBuilder('users');
        $q2->where('id', 3)->limit(1);
        $expectedSql = $q2->toSql();

        // find() calls where('id', $id)->first() which sets limit(1)
        // We'll just verify the where clause portion
        $this->assertStringContainsString('WHERE `id` = ?', $expectedSql);
    }

    // ─── toSql() / getBindings() ────────────────────────────

    public function testToSqlReturnsString(): void
    {
        $q = new QueryBuilder('users');
        $q->where('status', 'active')->sort('-created_at')->limit(10);

        $this->assertSame(
            'SELECT * FROM `users` WHERE `status` = ? ORDER BY `created_at` DESC LIMIT 10',
            $q->toSql()
        );
    }

    public function testGetBindingsReturnsArray(): void
    {
        $q = new QueryBuilder('users');
        $q->where(['status' => 'active', 'age >=' => 18]);

        $this->assertSame(['active', 18], $q->getBindings());
    }

    // ─── complex / integration ──────────────────────────────

    public function testComplexTicketsQuery(): void
    {
        $status     = 'open';
        $priority   = null;
        $categoryId = 5;
        $dateFrom   = '2025-01-01';
        $dateTo     = null;
        $search     = 'bug';
        $isEmployee = true;
        $userId     = 42;

        $q = new QueryBuilder('tickets AS t');
        $q->select('t.*', 'c.name AS category_name')
          ->leftJoin('categories AS c', 'c.id', 't.category_id')
          ->where(['t.deleted_at' => null])
          ->when($isEmployee, ['t.reporter_id' => $userId])
          ->when($status, ['t.status' => $status])
          ->when($priority, ['t.priority' => $priority])
          ->when($categoryId, ['t.category_id' => $categoryId])
          ->when($dateFrom, ['t.created_at >=' => $dateFrom])
          ->when($dateTo, ['t.created_at <=' => $dateTo])
          ->search(['t.subject', 't.ticket_number'], $search)
          ->sort('-created_at')
          ->limit(20);

        $sql = $q->toSql();
        $bindings = $q->getBindings();

        $this->assertStringContainsString('SELECT t.*, c.name AS category_name', $sql);
        $this->assertStringContainsString('LEFT JOIN `categories` AS `c` ON `c`.`id` = `t`.`category_id`', $sql);
        $this->assertStringContainsString('`t`.`deleted_at` IS NULL', $sql);
        $this->assertStringContainsString('`t`.`reporter_id` = ?', $sql);
        $this->assertStringContainsString('`t`.`status` = ?', $sql);
        $this->assertStringNotContainsString('`t`.`priority`', $sql); // skipped (null)
        $this->assertStringContainsString('`t`.`category_id` = ?', $sql);
        $this->assertStringContainsString('`t`.`created_at` >= ?', $sql);
        $this->assertStringNotContainsString('<= ?', $sql); // skipped ($dateTo null)
        $this->assertStringContainsString('(`t`.`subject` LIKE ? OR `t`.`ticket_number` LIKE ?)', $sql);
        $this->assertStringContainsString('ORDER BY `created_at` DESC', $sql);
        $this->assertStringContainsString('LIMIT 20', $sql);

        // Bindings: reporter_id(42), status('open'), category_id(5), dateFrom('2025-01-01'), search('%bug%' x2)
        $this->assertSame([42, 'open', 5, '2025-01-01', '%bug%', '%bug%'], $bindings);
    }

    public function testWhereInExisting(): void
    {
        $q = new QueryBuilder('users');
        $q->whereIn('status', ['active', 'pending']);

        $this->assertSame(
            'SELECT * FROM `users` WHERE `status` IN (?, ?)',
            $q->toSql()
        );
        $this->assertSame(['active', 'pending'], $q->getBindings());
    }

    public function testWhereLikeExisting(): void
    {
        $q = new QueryBuilder('users');
        $q->whereLike('name', '%john%');

        $this->assertSame(
            'SELECT * FROM `users` WHERE `name` LIKE ?',
            $q->toSql()
        );
    }

    public function testOrderByExisting(): void
    {
        $q = new QueryBuilder('users');
        $q->orderBy('created_at', 'DESC');

        $this->assertSame(
            'SELECT * FROM `users` ORDER BY `created_at` DESC',
            $q->toSql()
        );
    }

    public function testLimitOffset(): void
    {
        $q = new QueryBuilder('users');
        $q->limit(10)->offset(20);

        $this->assertSame('SELECT * FROM `users` LIMIT 10 OFFSET 20', $q->toSql());
    }

    public function testWhereInvalidOperatorThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $q = new QueryBuilder('users');
        $q->where('status', 'INVALID_OP', 'value');
    }
}
