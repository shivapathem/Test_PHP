USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_mod_LeaveApplications]    Script Date: 11/09/2025 21:38:30 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER      PROCEDURE [dbo].[usp_mod_LeaveApplications]
@dDate				DATE,
@Login				NVARCHAR(100),
@LeaveTypesID		INT,
@ShortNotice		BIT,
@oversummer			TINYINT,
@isOK				BIT,
@History			VARCHAR(MAX),
@currentuser		INT,
@status				INT OUTPUT,
@SchedulingPersonID INT,
@LeaveStartTime		INT = NULL,
@LeaveEndTime		INT = NULL,
@LeaveStartTimeLocal DATETIME = NULL,
@LeaveEndTimeLocal DATETIME = NULL
AS
BEGIN

   DECLARE  @LeaveID INT,
			@AllocationsSPID INT,
			@allocationid INT,
			@AllocationsDutyID INT,
			@PublishStatus tinyint=0,
			@ReturnValue INT,
			@HistoryTypeLeave INT,
			@HistoryTypePerson INT;

   SET NOCOUNT ON;
   SET DATEFORMAT YMD;

   BEGIN TRY

	SELECT @LeaveID = ID 
	  FROM LeaveApplications (NOLOCK) 
	 WHERE SchedulingPersonID = @SchedulingPersonID
	   AND dDate = @dDate; 
  
	 SELECT @HistoryTypePerson = ID
	   FROM HistoryTypes
	  WHERE HistoryType = 'AllocationScheduledPerson'

	 SELECT @HistoryTypeLeave = ID
	   FROM HistoryTypes
	  WHERE HistoryType = 'LeaveApplication'
	 
	 SELECT @allocationid = AL_AllocationsID, 
			@AllocationsSPID = ASP_AllocationsSPID,
			@AllocationsDutyID = ASP_AllocationsDutyID
	   FROM Allocations  
	  INNER JOIN ScheduledPersonTeam_LINK sptl ON AL_SchedulingTeamID = sptl.TeamID
	  INNER JOIN TimeDimension td ON td.ixYearWeek = AL_WeekNumber
	   LEFT JOIN AllocationsScheduledPersons ON AL_AllocationsID = ASP_AllocationsID 
											AND ASP_DutyDate = td.dDateTime 
											AND sptl.ScheduledPersonID = ASP_SchedulingPersonID 
	  WHERE sptl.ScheduledPersonID = @SchedulingPersonID 
	    AND td.dDateTime =  @dDate
	    AND sptl.IsHomeTeam = 1
	    AND sptl.scheduledType = 1
	    AND @dDate BETWEEN sptl.StartDate AND sptl.EndDate
	  
	  IF ( ISNULL(@allocationid,0) > 0 )
	    BEGIN

		  IF(isNULL(@AllocationsSPID, 0) = 0)
		   BEGIN
			EXEC @ReturnValue = usp_CreateScheduledPerson   @allocationid,
															@AllocationsDutyID,
															@SchedulingPersonID,
															@dDate,
															@Login,
															@AllocationsSPID = @AllocationsSPID OUTPUT

		   END
	  
		  IF ( ISNULL(@AllocationsSPID,0) > 0 )
		   BEGIN

			UPDATE AllocationsScheduledPersons 
			   SET ASP_LeaveType = 2 , 
				   ASP_LeaveStatus = 0,
				   ASP_LeaveDuration = 0,
				   ASP_UpdatedBy = @currentuser,
				   ASP_UpdatedDate = GETUTCDATE()
			 WHERE ASP_AllocationsSPID = @AllocationsSPID 
		   
		   END

		  EXEC @ReturnValue = usp_CreateAllocationsUpdate @allocationid,
														  @AllocationsDutyID,
														  @AllocationsSPID,
														  @PublishStatus,
														  @Login;
 
	  END

   IF ( ISNULL(@LeaveID,0) > 0 )   
	BEGIN              
			  
		 UPDATE LeaveApplications
			SET LeaveTypesID = @LeaveTypesID, 
				LastModDate = getutcdate(),
				LastModBy =  @currentuser,
				Approved = 0, 
				Deleted = 0, 
				Sent = 0, 
				Attention = 0, 
				ShortNotice = @ShortNotice, 
				unlikely = 0, 
				oversummer = @oversummer, 
				isOK = @isOK,
				CountLeave = 1,
				LeaveStartTime = @LeaveStartTime,
				LeaveEndTime = @LeaveEndTime,
				LeaveStartDateTime = @LeaveStartTimeLocal,
				LeaveEndDateTime = @LeaveEndTimeLocal,
				UserEmailSent = @ShortNotice,
				LeaveTypeID = 2
		  WHERE SchedulingPersonID = @SchedulingPersonID 
			AND dDate = @dDate 

		EXEC [usp_mod_AllocationHistory] @LeaveID,@HistoryTypeLeave,@currentuser,@History,1;

		IF (ISNULL(@AllocationsSPID,0) > 0)
			EXEC [usp_mod_AllocationHistory] @AllocationsSPID,@HistoryTypePerson,@currentuser,@History,1,'PH';

		SET @status = @LeaveID;

		SELECT @status intstatus;

		RETURN 0;

	END
   ELSE
   	BEGIN
		INSERT INTO  LeaveApplications
					(   Login, 
						dDate, 
						LeaveTypesID, 
						ShortNotice, 
						Created,
						CreatedBy,
						LastModDate,
						LastModBy, 
						oversummer, 
						isOK,
						CountLeave,
						SchedulingPersonID,
						LeaveStartTime,
						LeaveEndTime,
						UserEmailSent,
						LeaveTypeID,
						LeaveStartDateTime,
						LeaveEndDateTime
					)
					VALUES 
					(
						@Login,
						@dDate,
						@LeaveTypesID,
						@ShortNotice,
						getutcdate(),
						@currentuser,
						getutcdate(),
						@currentuser,
						@oversummer,
						@isOK,
						1,
						@SchedulingPersonID,
						@LeaveStartTime,
						@LeaveEndTime,
						@ShortNotice,
						2,
						@LeaveStartTimeLocal,
						@LeaveEndTimeLocal
					)

		   SET @LeaveID =SCOPE_IDENTITY();

		   EXEC [usp_mod_AllocationHistory] @LeaveID,15,@currentuser,@History,1;

		   IF (ISNULL(@AllocationsSPID,0) > 0)
			  EXEC [usp_mod_AllocationHistory] @AllocationsSPID,@HistoryTypePerson,@currentuser,@History,1,'PH';

		   SET @status = @LeaveID;

		   SELECT @status intstatus;
		   
		   RETURN 0;

	  END
			
	END TRY
				
	BEGIN CATCH

		INSERT INTO ErrorLog
			(ErrorNumber,
				ErrorState,
				ErrorSeverity,
				ErrorProcedure,
				ErrorLine,
				ErrorMessage,
				ErrorDateTime,
				UserName
			)
		SELECT ERROR_NUMBER() AS ErrorNumber,
			ERROR_STATE() AS ErrorState,
			ERROR_SEVERITY() AS ErrorSeverity,
			ERROR_PROCEDURE() AS ErrorProcedure,
			ERROR_LINE() AS ErrorLine,
			ERROR_MESSAGE() AS ErrorMessage,
			getutcdate(),
			@currentuser	

	 	 RETURN 1 

	END CATCH;		

END