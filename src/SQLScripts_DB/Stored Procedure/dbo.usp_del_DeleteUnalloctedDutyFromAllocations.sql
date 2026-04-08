USE [ALLOCATE7]

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

--Declare variable
DECLARE @strSQL NVARCHAR(max)

--Check if the stored procedure already exists or not so we can either use ALTER or CREATE for the script
IF EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[usp_del_DeleteUnalloctedDutyFromAllocations]') AND type in (N'P', N'PC'))
SET @strSQL = N'ALTER '
ELSE
SET @strSQL = N'CREATE '

--Execute the script statement
Set @strSQL= @strSQL + N'PROCEDURE [dbo].[usp_del_DeleteUnalloctedDutyFromAllocations]
@weeknumber INT,
@dutyid INT,
@iday INT,
@dayvalue INT

AS
BEGIN
    -- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	declare @status int
	declare @returnstring varchar(30)
	set @returnstring ='''';
	set @status =1;
	
	SET NOCOUNT ON;
	BEGIN TRY 
			DELETE from Allocations where ID IN (select top(@dayvalue) ID from Allocations 
		where SchedulingPersonID IS NULL AND  MasterDutyId = @dutyid 
		and isActiveDuty = 1 and WeekNumber = @weeknumber and iDay = @iday
		ORDER BY ID desc)
		
	END TRY
BEGIN CATCH
		REVERT;
		set @status =0;
		set @returnstring = ''Due to some error not able to complete this action.''
	
	END CATCH
	select @status intstaus, @returnstring returnstatus
END
'

EXEC dbo.sp_executesql @strSQL

GO