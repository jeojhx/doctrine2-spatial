<?php
/**
 * Copyright (C) 2015 Derek J. Lambert
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace CrEOF\Spatial\Tests\ORM\Query\AST\Functions\PostgreSql;

use CrEOF\Spatial\PHP\Types\Geometry\LineString;
use CrEOF\Spatial\PHP\Types\Geometry\Point;
use CrEOF\Spatial\PHP\Types\Geometry\Polygon;
use CrEOF\Spatial\Tests\Fixtures\PolygonEntity;
use CrEOF\Spatial\Tests\OrmTestCase;
use Doctrine\ORM\Query;

/**
 * ST_Covers DQL function tests
 *
 * @author  Derek J. Lambert <dlambert@dereklambert.com>
 * @license http://dlambert.mit-license.org MIT
 *
 * @group dql
 */
class STCoversTest extends OrmTestCase
{
    protected function setUp()
    {
        $this->usesEntity(self::POLYGON_ENTITY);
        $this->supportsPlatform('postgresql');

        parent::setUp();
    }

    /**
     * @group geometry
     */
    public function testSelectSTCovers()
    {
        $lineString1 = new LineString(array(
            new Point(6, 6),
            new Point(10, 6),
            new Point(10, 10),
            new Point(6, 10),
            new Point(6, 6)
        ));
        $lineString2 = new LineString(array(
            new Point(5, 5),
            new Point(7, 5),
            new Point(7, 7),
            new Point(5, 7),
            new Point(5, 5)
        ));
        $entity1 = new PolygonEntity();

        $entity1->setPolygon(new Polygon(array($lineString1)));
        $this->getEntityManager()->persist($entity1);

        $entity2 = new PolygonEntity();

        $entity2->setPolygon(new Polygon(array($lineString2)));
        $this->getEntityManager()->persist($entity2);
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();

        $query  = $this->getEntityManager()->createQuery('SELECT p, ST_Covers(p.polygon, ST_GeomFromText(:p1)) FROM CrEOF\Spatial\Tests\Fixtures\PolygonEntity p');

        $query->setParameter('p1', 'POLYGON((5 5,7 5,7 7,5 7,5 5))', 'string');

        $result = $query->getResult();

        $this->assertCount(2, $result);
        $this->assertEquals($entity1, $result[0][0]);
        $this->assertFalse($result[0][1]);
        $this->assertEquals($entity2, $result[1][0]);
        $this->assertTrue($result[1][1]);
    }

    /**
     * @group geometry
     */
    public function testSTCoversWhereParameter()
    {
        $lineString1 = new LineString(array(
            new Point(6, 6),
            new Point(10, 6),
            new Point(10, 10),
            new Point(6, 10),
            new Point(6, 6)
        ));
        $lineString2 = new LineString(array(
            new Point(5, 5),
            new Point(7, 5),
            new Point(7, 7),
            new Point(5, 7),
            new Point(5, 5)
        ));
        $entity1 = new PolygonEntity();

        $entity1->setPolygon(new Polygon(array($lineString1)));
        $this->getEntityManager()->persist($entity1);

        $entity2 = new PolygonEntity();

        $entity2->setPolygon(new Polygon(array($lineString2)));
        $this->getEntityManager()->persist($entity2);
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();

        $query  = $this->getEntityManager()->createQuery('SELECT p FROM CrEOF\Spatial\Tests\Fixtures\PolygonEntity p WHERE ST_Covers(p.polygon, ST_GeomFromText(:p1)) = true');

        $query->setParameter('p1', 'POLYGON((5 5,7 5,7 7,5 7,5 5))', 'string');

        $result = $query->getResult();

        $this->assertCount(1, $result);
        $this->assertEquals($entity2, $result[0]);
    }
}
