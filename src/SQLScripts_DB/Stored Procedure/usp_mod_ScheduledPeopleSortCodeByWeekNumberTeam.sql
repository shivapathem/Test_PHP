USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_mod_ScheduledPeopleSortCodeByWeekNumberTeam]    Script Date: 12/12/2025 15:49:03 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- =============================================
-- Author:		<Author,,Name>
-- Create date: <Create Date,,>
-- Description:	<Description,,>
-- =============================================
CREATE OR ALTER     PROCEDURE [dbo].[usp_mod_ScheduledPeopleSortCodeByWeekNumberTeam]
	-- Add the parameters for the stored procedure here
	@sortcode varchar(30),
	@scheduledpersonid int,
	@allocateId int,
	@teamid int,
	@current_User_Id int
AS
BEGIN

	SET NOCOUNT ON;

	DECLARE @returnstring		VARCHAR(100)='success',
			@status				int=1,
			@DutyDate			DATE,
			@ReturnValue		INT,
			@NetLogin			VARCHAR(30),
			@AllocationsSPID	INT,
			@AllocationsDutyID	INT,
			@AAP_ID				INT;

	DECLARE @TempID TABLE ( ID INT);

	BEGIN TRANSACTION
	 BEGIN TRY

		SELECT @NetLogin = UD_NetLogin
			FROM UserDetails
			WHERE UD_UserID = @current_User_Id

	 IF EXISTS (   SELECT top 1 AL.AL_AllocationsID
					 FROM Allocations AL
					INNER JOIN TimeDimension TD on TD.ixYearWeek = AL.AL_WeekNumber
					INNER JOIN ScheduledPersonTeam_LINK SL on SL.TeamID = AL.AL_SchedulingTeamID
					WHERE SL.ScheduledPersonID = @scheduledpersonid
					  AND SL.scheduledType = 1
					  AND SL.IsHomeTeam = 1
					  AND TD.dDateTime BETWEEN SL.StartDate and SL.EndDate
					  AND AL.AL_AllocationsID =  @allocateId 
				)
	  BEGIN

		Update AllocationsScheduledPersons 
		   SET ASP_SortCode = @sortcode 
		OUTPUT inserted.ASP_AllocationsSPID INTO @TempID 
		 where ASP_SchedulingPersonID = @scheduledpersonid 
		   and ASP_AllocationsID = @allocateId 

		 SELECT @AllocationsSPID = ASP_AllocationsSPID,
				@AllocationsDutyID = ASP_AllocationsDutyID
			FROM AllocationsScheduledPersons AP
			INNER JOIN @TempID TP on TP.ID = AP.ASP_AllocationsSPID
			WHERE TP.ID > 0

		IF ( @@ROWCOUNT = 0 )
		 BEGIN

				SELECT @DutyDate = TD.dDateTime
				  FROM Allocations AL
				 INNER JOIN TimeDimension TD on TD.ixYearWeek = AL.AL_WeekNumber
				 WHERE AL.AL_AllocationsID =  @allocateId
				   AND TD.ixDayInWeek = 0

				EXEC @ReturnValue = usp_CreateScheduledPerson   @allocateId,
																0,
																@scheduledpersonid,
																@DutyDate,
																@NetLogin,
																@AllocationsSPID = @AllocationsSPID OUTPUT

				IF ( ISNULL(@ReturnValue,0) > 0 )
				 BEGIN
					SET @returnstring = 'Oops! Some Error Found.';
			
					SET @status = 0;

					SELECT @status intstatus, @returnstring strstatus ;
					
					ROLLBACK TRANSACTION

					return;
				 END

				Update AllocationsScheduledPersons 
				   SET ASP_SortCode = @sortcode 
				 where ASP_AllocationsSPID = @AllocationsSPID

		 END

	   END
	 ELSE
	  BEGIN
	   
		    UPDATE AllocationsAddPersons
			   SET AAP_SortCode = @sortcode 
			OUTPUT inserted.AAP_AllocationsSPID INTO @TempID 
			 WHERE AAP_AllocationsID = @allocateId
			   AND AAP_SchedulingPersonID = @scheduledpersonid

			SELECT @AllocationsSPID = ASP_AllocationsSPID,
				   @AllocationsDutyID = ASP_AllocationsDutyID
			  FROM AllocationsScheduledPersons AP
			 INNER JOIN @TempID TP on TP.ID = AP.ASP_AllocationsSPID
			 WHERE TP.ID > 0


			IF ( @@ROWCOUNT = 0 )
			 BEGIN

					SELECT @DutyDate = TD.dDateTime
					  FROM Allocations AL
					 INNER JOIN TimeDimension TD on TD.ixYearWeek = AL.AL_WeekNumber
					 WHERE AL.AL_AllocationsID =  @allocateId
					   AND TD.ixDayInWeek = 0

					INSERT INTO AllocationsAddPersons
								( AAP_AllocationsID,
								  AAP_SchedulingPersonID,
								  AAP_Status,
								  AAP_iDay,
								  AAP_DutyDate,
								  AAP_SortCode,
								  AAP_CreatedBy,
								  AAP_CreatedDate
								 )
						VALUES (
								 @allocateId,
								 @scheduledpersonid,
								 1,
								 0,
								 @DutyDate,
								 @sortcode,
								 @current_User_Id,
								 GETUTCDATE()
								)

			 END
		   
	   END


					EXEC @ReturnValue = usp_CreateAllocationsUpdate @allocateId,
													 @AllocationsDutyID,
													 @AllocationsSPID,
													 0,
													 @NetLogin;

					IF ( ISNULL(@ReturnValue,1) <> 0 )
					 BEGIN
						SET @returnstring = 'Error while creating Duty update status.';
			
						SET @status = 0;
					 END


		COMMIT TRANSACTION

			SELECT @status intstatus, @returnstring strstatus,@sortcode sortcode,
				   @scheduledpersonid scheduledpersonid, 
				   @allocateId allocateId, @teamid teamid ;

			return;
	END TRY
	BEGIN CATCH
	  
		 ROLLBACK TRANSACTION

		    INSERT INTO ErrorLog
			 VALUES
			  (
			   ERROR_NUMBER(),
			   ERROR_STATE(),
			   ERROR_SEVERITY(),
			   ERROR_LINE(),
			   'usp_mod_ScheduledPeopleSortCodeByWeekNumberTeam',
			   ERROR_MESSAGE(),
			   GETDATE(),
			   @current_User_Id
			  )

			SET @returnstring = 'Oops! Some Error Found.';
			
			SET @status = 0;

			SELECT @status intstatus, @returnstring strstatus ;
			
			return;
	
	END CATCH

END