# Test de discounts en cascade en utilisant les fonctionalités de récursivité de Mysql 8

# Jeu de test
```sql
DROP TABLE IF EXISTS `conditions`;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `conditions` (
  `ID` int(11) NOT NULL,
  `TARGET` varchar(100) DEFAULT NULL,
  `VALUE` varchar(100) DEFAULT NULL,
  `PARENT_ID` int(11) DEFAULT NULL,
  `ID_DISCOUNT` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `PARENT_ID` (`PARENT_ID`),
  CONSTRAINT `conditions_ibfk_1` FOREIGN KEY (`PARENT_ID`) REFERENCES `conditions` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
INSERT INTO `conditions` VALUES (29,'Weight','20',198,NULL),(72,'Size','18',29,1),(123,'Shape','Round',692,2),(198,'Color','Red',333,NULL),(333,'Color','Blue',NULL,NULL),(692,'Weight','10',333,NULL),(4610,'Size','15',29,3),(5010,'Size','25',333,4),(5050,'Shape','Square',692,5);
```
```sql
DROP TABLE IF EXISTS `discounts`;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `discounts` (
  `id_discount` int(11) NOT NULL,
  `value` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_discount`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
INSERT INTO `discounts` VALUES (1,15),(2,20),(3,30),(4,40),(5,50);
```
# Requete de recursivité mysql
```sql
WITH RECURSIVE CONDITIONS_TREE(ID, TARGET, VALUE, PATH, ID_DISCOUNT)
AS
(
	SELECT ID, TARGET, VALUE, CONCAT(TARGET, ":", VALUE) , ID_DISCOUNT
	FROM CONDITIONS
	WHERE PARENT_ID IS NULL
	UNION ALL
	SELECT S.ID, S.TARGET, S.VALUE, CONCAT(M.PATH, ",", S.TARGET, ":", S.VALUE), S.ID_DISCOUNT
	FROM CONDITIONS_TREE M JOIN CONDITIONS S ON M.ID=S.PARENT_ID
	/* Product is color is blue */
	WHERE (S.TARGET = 'Color' AND S.VALUE = 'Blue'
	OR S.TARGET != 'Color')
	/* Product size is 25 */
	AND (S.TARGET = 'Size' AND S.VALUE = '25'
	OR S.TARGET != 'Size')
	/* Product shape is square */
	AND (S.TARGET = 'Shape' AND S.VALUE = 'Square'
	OR S.TARGET != 'Shape')
)

/*SELECT ID_DISCOUNT, PATH FROM CONDITIONS_TREE;*/
SELECT ID_DISCOUNT, VALUE FROM discounts WHERE ID_DISCOUNT IN (SELECT ID_DISCOUNT FROM CONDITIONS_TREE)
```
