USE [ALLOCATE7]

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

--Declare variable
DECLARE @strSQL NVARCHAR(max)

--Check if the stored procedure already exists or not so we can either use ALTER or CREATE for the script
IF EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[usp_SwapAssignAllocationsById]') AND type in (N'P', N'PC'))
SET @strSQL = N'ALTER '
ELSE
SET @strSQL = N'CREATE '

--Execute the script statement
Set @strSQL= @strSQL + N'PROCEDURE [dbo].[usp_SwapAssignAllocationsById]
@oldAllocationId INT,
@oldScheduledPersonId INT,
@newAllocationId INT,
@newScheduledPersonId INT,
@teamId INT,
@userId INT,
@srcHistory VARCHAR(1000),
@desHistory VARCHAR(1000),
@oldPersonComments VARCHAR(500),
@newPersonComments VARCHAR(500)

AS
BEGIN
    -- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;
	DECLARE @err int, @rows int, @jobCount int, @status varchar(100), @returnstring varchar(200) 
	SET @status = ''success''
	SET @returnstring = ''Allocations updated successfully''
   
  
		BEGIN TRANSACTION
	--update schPersonId for old allocation
		UPDATE Allocations
		SET
		SchedulingPersonID = @newScheduledPersonId,
		PersonComments = @newPersonComments
		WHERE (ID = @oldAllocationId)

		EXEC [usp_mod_AllocationHistory] @oldAllocationId,8,@userID,@srcHistory,1
		
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

		--update schPersonId for jobs of old allocation
		SELECT @jobCount = COUNT(ID) FROM Allocations_jobs
		WHERE AllocationID = @oldAllocationId
			
		IF @jobCount <> 0
			BEGIN
				UPDATE Allocations_jobs
				SET
				SchedulingPersonID = @newScheduledPersonId
				WHERE AllocationID = @oldAllocationId AND schedulingTeamId = @teamId
			
				--check error
				SELECT @err = @@ERROR --, @rows = @@ROWCOUNT
				IF @err <> 0 
					BEGIN
						ROLLBACK TRANSACTION
						SET @status = ''error''
						SET @returnstring = ''Error updating the old Allocations jobs Details.''
						RETURN 0
					END
				IF @rows = 0 
					BEGIN
						ROLLBACK TRANSACTION
						SET @status = ''error''
						SET @returnstring = ''Error updating the old Allocations jobs Details.''
						RETURN 0
					END
			END
		IF @newAllocationId <> 0
			BEGIN
				--update schPersonId for new allocation
				UPDATE Allocations
				SET
				SchedulingPersonID = @oldScheduledPersonId,
				PersonComments = @oldPersonComments
				WHERE ID = @newAllocationId	

				EXEC [usp_mod_AllocationHistory] @newAllocationId,8,@userID,@desHistory,1
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
				--update schPersonId for jobs of new allocation
				SELECT @jobCount = COUNT(ID) FROM Allocations_jobs
				WHERE AllocationID = @oldAllocationId
			
				IF @jobCount <> 0
					BEGIN
						UPDATE Allocations_jobs
						SET  
						SchedulingPersonID = @oldScheduledPersonId
						WHERE  (AllocationID = @newAllocationId AND schedulingTeamId = @teamId)
					

						--check error
						SELECT @err = @@ERROR--, @rows = @@ROWCOUNT
						IF @err <> 0 
							BEGIN
								ROLLBACK TRANSACTION
								SET @status = ''error''
								SET @returnstring = ''Error updating the new Allocations jobs Details.''
								RETURN 0
							END
						IF @rows = 0 
							BEGIN
								ROLLBACK TRANSACTION
								SET @status = ''error''
								SET @returnstring = ''Error updating the new Allocations jobs Details.''
								RETURN 0
							END
					END
			END
		
	--SELECT @status strstatus , @returnstring strreturnstring;
	COMMIT TRANSACTION
END
'

EXEC dbo.sp_executesql @strSQL

GO