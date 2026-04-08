USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_add_LeaveinAllocation]    Script Date: 17/02/2022 14:33:30 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
DECLARE @strSQL NVARCHAR(max)

--Check if the stored procedure already exists or not so we can either use ALTER or CREATE for the script
IF EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[usp_add_LeaveinAllocation]') AND type in (N'P', N'PC'))
SET @strSQL = N'ALTER '
ELSE
SET @strSQL = N'CREATE '

--Execute the script statement
Set @strSQL= @strSQL + N' PROCEDURE [dbo].[usp_add_LeaveinAllocation] 
	-- Add the parameters for the stored procedure here
	@SchedulingPersonID int,
	@DutyDate varchar(50),
	@LeaveID  int,
	@zeroLeave int
	
AS
BEGIN
   Declare
   @WeekNumber int,
   @iDay int,
   @DutyText varchar(20),
   @AllocationID int,
   @teamId int,
   @dutyColorId int
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;

	 if @zeroLeave = 1
	   BEGIN
			SET @DutyText =''OFF Leave''
	   END
	else
	BEGIN 
	   SET @DutyText =''Leave''
	END

	SELECT @AllocationID=ID,@teamId=SchedulingTeamId FROM Allocations WHERE SchedulingPersonID=@SchedulingPersonID and DutyDate=@DutyDate;

	select @WeekNumber=ixYearWeek,@iDay=ixDayInWeek from TimeDimension where dDateTime=CONVERT(datetime,@DutyDate, 101);

	select TOP 1 @dutyColorId=MasterDutyColourID from REF_MasterDutyColours where ColourName=''Leave''

	IF(@AllocationID!='''')
	BEGIN
	INSERT INTO Allocations
	  (DutyName, Duration, WeekNumber, iDay, StartTime, EndTime,LeaveID, 
	  SchedulingTeamId,SchedulingPersonID,isEdited,isActive,OrigAllocationID,
	  DutyDate,StartDate,EndDate,UnAllocated,aftermidnight,dutyColorId,AllocationID)
	   SELECT  @DutyText, 0, WeekNumber, iDay,'''', '''',@LeaveID, 
	  SchedulingTeamId,SchedulingPersonID,0,isActive,ID,
	  DutyDate,'''','''',0,0,@dutyColorId,0 FROM  Allocations WHERE (ID = @AllocationID);
	
  UPDATE Allocations SET SchedulingPersonID = '''',UnAllocated=1 WHERE(ID = @AllocationID);

  UPDATE Allocations_jobs SET SchedulingPersonID = '''' WHERE(AllocationID = @AllocationID);

	END

ELSE
	BEGIN

INSERT INTO Allocations(AllocationID,Duration,WeekNumber,
iDay,dutyName,StartTime,EndTime,LeaveID,
schedulingTeamId,SchedulingPersonID,DutyDate,StartDate,EndDate,UnAllocated,aftermidnight,isEdited,dutyColorId)
values(0,0,@WeekNumber,@iDay,@DutyText,'''','''',@LeaveID,
@teamId,@SchedulingPersonID,CONVERT(datetime,@DutyDate, 101),'''','''',0,0,0,@dutyColorId)
  
     END


END
'
EXEC dbo.sp_executesql @strSQL

GO