SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

DECLARE @checkAllocateIns VARCHAR(10)
DECLARE @checkDepartmentID VARCHAR(10)

SET @checkAllocateIns = (SELECT object_definition(default_object_id) AS definition
FROM   sys.columns
WHERE  name      ='AllocateInstanceID'
AND    object_id = object_id('dbo.Allocations'))
IF(ISNULL(@checkAllocateIns,'' )= '')
BEGIN
    ALTER TABLE Allocations ADD DEFAULT(0) FOR AllocateInstanceID
END

SET @checkDepartmentID = (SELECT object_definition(default_object_id) AS definition
FROM   sys.columns
WHERE  name      ='DepartmentID'
AND    object_id = object_id('dbo.Allocations'))
IF(ISNULL(@checkDepartmentID,'' )= '')
BEGIN
    ALTER TABLE Allocations ADD DEFAULT(0) FOR DepartmentID
END