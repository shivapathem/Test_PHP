USE [ALLOCATE7]

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

--Declare variable
DECLARE @strSQL NVARCHAR(max)

--Check if the stored procedure already exists or not so we can either use ALTER or CREATE for the script
IF EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[usp_get_AllocationPerDayUnalloacted]') AND type in (N'P', N'PC'))
SET @strSQL = N'ALTER '
ELSE
SET @strSQL = N'CREATE '

--Execute the script statement
Set @strSQL= @strSQL + N'PROCEDURE [dbo].[usp_get_AllocationPerDayUnalloacted]
@dayvalue int,
@teamid INT,
@weeknumber INT,
@dutyid INT,
@iday INT

AS
BEGIN
    -- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;
	

select CASE WHEN  @dayvalue -count(*) = 0 or   @dayvalue -count(*) < 0 THEN 0 ELSE  @dayvalue -count(*)  END as AllocationDayCount, @dayvalue -count(*) as actualcount,count(*) as allocationvalue,@dayvalue as dutyday from allocations where SchedulingTeamId = @teamid and WeekNumber =@weeknumber and iDay = @iday  AND MasterDutyId = @dutyid and isActiveDuty =1
END
'

EXEC dbo.sp_executesql @strSQL

GO