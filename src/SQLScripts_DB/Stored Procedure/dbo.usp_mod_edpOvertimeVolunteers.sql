USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_mod_edpOvertimeVolunteers]    Script Date: 28/08/2025 19:17:44 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER   PROCEDURE [dbo].[usp_mod_edpOvertimeVolunteers]
@edpId					INT,
@ddate					DATE,
@AllocationsSPID		INT = 0,
@schedulingPersonId		INT,
@comments				VARCHAR(500),
@userId					INT,
@username				VARCHAR(100),
@action					VARCHAR(100),
@status					INT OUTPUT,
@returnstring			VARCHAR(500) OUTPUT

AS
BEGIN

	SET NOCOUNT ON;

	DECLARE @err INT,
	        @rows INT, 
			@History VARCHAR(500),
			@AllocationUpdFlag BIT = 0,
			@AllocationsID	INT,
			@ReturnValue	INT,
			@NetLogin		VARCHAR(20);

	SELECT @NetLogin = UD_NetLogin
	  FROM UserDetails
	 WHERE UD_UserID = @userId
	
	BEGIN TRANSACTION
		IF(@action = 'insert')
			BEGIN

				INSERT INTO edp (ddate,SchedulingTeamId,SchedulingPersonId)
				VALUES(@ddate, 0, @schedulingPersonId)

				SET @edpId = @@IDENTITY

				SET @History = 'Availability added by '+@username+' on '+FORMAT(Getdate(),'dd/MM/yyyy HH:mm');

				IF ( ISNULL(@AllocationsSPID,0) > 0 )
				  BEGIN
				    
					UPDATE AllocationsScheduledPersons 
					   SET ASP_EDPStatus = 1
					 WHERE ASP_AllocationsSPID = @AllocationsSPID

					IF ( @@ROWCOUNT > 0 )
					 SET @AllocationUpdFlag = 1

				  END

				IF ( @AllocationUpdFlag = 0 ) 
				  BEGIN

					UPDATE AllocationsScheduledPersons 
					   SET ASP_EDPStatus = 1
					 WHERE ASP_SchedulingPersonID = @schedulingPersonId
					   AND ASP_DutyDate = @ddate

					IF ( @@ROWCOUNT > 0 )
					 SET @AllocationUpdFlag = 1

				  END

				IF ( @AllocationUpdFlag = 0 )
				 BEGIN

				   SELECT @AllocationsID = AL_AllocationsID
				     FROM Allocations AL
					INNER JOIN ScheduledPersonTeam_LINK SL ON SL.TeamID = AL_SchedulingTeamID
					INNER JOIN TimeDimension TD on TD.ixYearWeek = AL_WeekNumber
				    WHERE SL.ScheduledPersonID = @schedulingPersonId
					  AND SL.scheduledType = 1
					  AND SL.IsHomeTeam = 1
					  AND TD.dDateTime BETWEEN SL.StartDate and SL.EndDate
					  AND TD.dDateTime = @ddate

				   IF ( ISNULL(@AllocationsID,0) > 0)
				    BEGIN

						EXEC @ReturnValue = usp_CreateScheduledPerson  @AllocationsID,
														0,
														@SchedulingPersonID,
														@ddate,
														@NetLogin,
														@AllocationsSPID = @AllocationsSPID OUTPUT

						IF ( ISNULL(@ReturnValue,0) > 0 )
							BEGIN
								ROLLBACK TRANSACTION
								SET @status = 0
								SET @returnstring = 'Error while creating scheduled person allocation, Error inserting the edp details.'
								SELECT @status strstatus, @returnstring strsmsg
								RETURN 0
							END

						UPDATE AllocationsScheduledPersons 
						   SET ASP_EDPStatus = 1
						 WHERE ASP_AllocationsSPID = @AllocationsSPID

					END

				 END

				EXEC [usp_mod_AllocationHistory] @edpId,12,@userId,@History,1

				--check error
				SELECT @err = @@ERROR, @rows = @@ROWCOUNT
				IF @err <> 0 
					BEGIN
						ROLLBACK TRANSACTION
						SET @status = 0
						SET @returnstring = 'Error inserting the edp details.'
						SELECT @status strstatus, @returnstring strsmsg
						RETURN 0
					END
				IF @rows = 0 
					BEGIN
						ROLLBACK TRANSACTION
						SET @status = 0
						SET @returnstring = 'Error inserting the edp details.'
						SELECT @status strstatus, @returnstring strsmsg
						RETURN 0
					END
			END

		IF (@action = 'update')
			BEGIN

				UPDATE edp SET comments = @comments
				WHERE ID = @edpId

				SET @History = 'Comments added by '+@username+' on '+FORMAT(Getdate(),'dd/MM/yyyy HH:mm');

				EXEC [usp_mod_AllocationHistory] @edpId,12,@userId,@History,1

				--check error
				SELECT @err = @@ERROR, @rows = @@ROWCOUNT
				IF @err <> 0 
					BEGIN
						ROLLBACK TRANSACTION
						SET @status = 0
						SET @returnstring = 'Error updating the edp details.'
						SELECT @status strstatus, @returnstring strsmsg
						RETURN 0
					END
				IF @rows = 0 
					BEGIN
						ROLLBACK TRANSACTION
						SET @status = 0
						SET @returnstring = 'Error updating the edp details.'
						SELECT @status strstatus, @returnstring strsmsg
						RETURN 0
					END
			END

		IF (@action = 'delete')
			BEGIN

				SELECT @edpId = ID 
				  FROM edp 
				 WHERE SchedulingPersonId = @schedulingPersonId
				   AND ddate = @ddate
				 ORDER BY ID DESC

				DELETE FROM edp 
				WHERE SchedulingPersonId = @schedulingPersonId
				  AND ddate = @ddate

				If ( @@ROWCOUNT > 0 )
				 BEGIN

					IF ( ISNULL(@AllocationsSPID,0) > 0 )
					  BEGIN
				    
						UPDATE AllocationsScheduledPersons 
						   SET ASP_EDPStatus = 0
						 WHERE ASP_AllocationsSPID = @AllocationsSPID

					  END
					ELSE 
					  BEGIN

						UPDATE AllocationsScheduledPersons 
						   SET ASP_EDPStatus = 0
						 WHERE ASP_SchedulingPersonID = @schedulingPersonId
						   AND ASP_DutyDate = @ddate

					  END

				  END


			    SET @History = 'EDP deleted by '+@username+' on '+FORMAT(Getdate(),'dd/MM/yyyy HH:mm');

				EXEC [usp_mod_AllocationHistory] @edpId,12,@userId,@History,1

				--check error
				SELECT @err = @@ERROR, @rows = @@ROWCOUNT
				IF @err <> 0 
					BEGIN
						ROLLBACK TRANSACTION
						SET @status = 'error'
						SET @returnstring = 'Error updating the edp details.'
						SELECT @status strstatus, @returnstring strsmsg
						RETURN 0
					END
				IF @rows = 0 
					BEGIN
						ROLLBACK TRANSACTION
						SET @status = 'error'
						SET @returnstring = 'Error updating the edp details.'
						SELECT @status strstatus, @returnstring strsmsg
						RETURN 0
					END
			END		
			
	COMMIT TRANSACTION

	SET @status = CASE WHEN ISNULL(@edpId,0) > 0 THEN @edpId ELSE 1 END

	SET @returnstring = 'EDP '+@action+'ed successfully';

	SELECT @status strstatus, @returnstring strsmsg

END