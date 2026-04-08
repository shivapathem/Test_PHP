USE [ALLOCATE7]

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

--Declare variable
DECLARE @strSQL NVARCHAR(max)

--Check if the stored procedure already exists or not so we can either use ALTER or CREATE for the script
IF EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[usp_UnassignAllocation]') AND type in (N'P', N'PC'))
SET @strSQL = N'ALTER '
ELSE
SET @strSQL = N'CREATE '

--Execute the script statement
Set @strSQL= @strSQL + N'PROCEDURE [dbo].[usp_UnassignAllocation]
@allocationId INT,
@userId INT,
@username VARCHAR(100)

AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;

	DECLARE @err INT,@rows INT, @status INT, @dutyname VARCHAR(100), @returnstring VARCHAR(200), @jobCount INT, @jobId INT, @allocHistory VARCHAR(500), @jobHistory VARCHAR(500)

	SET @status = 1
	SET @returnstring = ''ok''
	SET @allocHistory = ''Duty unassigned(deleted) by ''+@username+'' on ''++CAST(GETDATE() AS VARCHAR);
	
	BEGIN TRANSACTION
		--update schPersonId to NULL
		UPDATE Allocations
		SET SchedulingPersonID = NULL
		WHERE ID = @allocationId

		EXEC [usp_mod_AllocationHistory] @allocationId,8,@userID,@allocHistory,1
		
		--check error
		SELECT @err = @@ERROR, @rows = @@ROWCOUNT
		IF @err <> 0 
			BEGIN
				ROLLBACK TRANSACTION
				SET @status = ''error''
				SET @returnstring = ''Error updating the Allocations Details.''
				RETURN 0
			END
		IF @rows = 0 
			BEGIN
				ROLLBACK TRANSACTION
				SET @status = ''error''
				SET @returnstring = ''Error updating the Allocations Details.''
				RETURN 0
			END

		--update schPersonId NULL for jobs of allocation
		SELECT @dutyname = DutyName FROM Allocations
		WHERE ID = @allocationId

		SELECT @jobCount = COUNT(ID) FROM Allocations_jobs
		WHERE AllocationID = @allocationId

		SET @jobHistory = ''This Job deleted because the duty '' +@dutyname+ ''was deleted by '' +@username+'' on '' +CAST(GETDATE() AS VARCHAR);
		IF @jobCount <> 0
			BEGIN
				UPDATE Allocations_jobs
				SET
				SchedulingPersonID = NULL
				WHERE AllocationID = @allocationId
			
				EXEC [usp_mod_AllocationHistory] @allocationId,8,@userID,@jobHistory,1
				--check error
				SELECT @err = @@ERROR, @rows = @@ROWCOUNT
				IF @err <> 0 
					BEGIN
						ROLLBACK TRANSACTION
						SET @status = ''error''
						SET @returnstring = ''Error updating the Allocations jobs .''
						RETURN 0
					END
				IF @rows = 0 
					BEGIN
						ROLLBACK TRANSACTION
						SET @status = ''error''
						SET @returnstring = ''Error updating the Allocations jobs.''
						RETURN 0
					END
			END

COMMIT TRANSACTION
END
'

EXEC dbo.sp_executesql @strSQL

GO