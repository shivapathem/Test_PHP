USE [ALLOCATE7]

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

--Declare variable
DECLARE @strSQL NVARCHAR(max)

--Check if the stored procedure already exists or not so we can either use ALTER or CREATE for the script
IF EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[usp_get_CheckDutyNameOnUnallocatedDuty]') AND type in (N'P', N'PC'))
SET @strSQL = N'ALTER '
ELSE
SET @strSQL = N'CREATE '

--Execute the script statement
Set @strSQL= @strSQL + N'PROCEDURE [dbo].[usp_get_CheckDutyNameOnUnallocatedDuty]
@masterdutyname VARCHAR(100),
@dutyid INT

AS
BEGIN
    -- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;
	DECLARE @action varchar(50)
	DECLARE @err int
	DECLARE @rows int
	DECLARE @status int
	SET @status = 1 

	set @action = ''updateunallocate''
IF not exists (select  * from Allocations where MasterDutyId = @dutyid and DutyName = @masterdutyname and isActiveDuty = 1)
	BEGIN
		set @action = ''addunallocate''
		BEGIN TRANSACTION	
				Update  Allocations set isActiveDuty = 0 where  MasterDutyId = @dutyid 
				SELECT @err = @@ERROR, @rows = @@ROWCOUNT
				IF @err <> 0 
					BEGIN
						ROLLBACK TRANSACTION
						RETURN @err
					END
				IF @rows = 0 
					BEGIN
						ROLLBACK TRANSACTION
						SET @status = 0
						SELECT @status retrstatus
						RETURN 0
					END
			COMMIT TRANSACTION
	END
	SELECT @action retrstatus
END

'

EXEC dbo.sp_executesql @strSQL

GO