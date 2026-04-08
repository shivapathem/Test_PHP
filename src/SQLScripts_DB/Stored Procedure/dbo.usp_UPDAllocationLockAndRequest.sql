USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_UPDAllocationLockAndRequest]    Script Date: 12/11/2025 14:14:29 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER PROCEDURE [dbo].[usp_UPDAllocationLockAndRequest]
	@ScheduledPersonID		INT,
	@WeekNumber				INT = NULL,
	@iDay					INT = NULL,
	@DutyDate				DATE = NULL,
	@UserID					INT

AS
BEGIN

    SET NOCOUNT ON

    SET DATEFORMAT YMD

	DECLARE @AllocationID			INT,
			@AllocationsSPID		INT,
			@ReturnValue			INT,
			@NetLogin				VARCHAR(25);

	SELECT @NetLogin = UD_NetLogin
	  FROM UserDetails
	 WHERE UD_UserID = @UserID

	IF ( @DutyDate IS NULL )
	  BEGIN
		SELECT @DutyDate = dDateTime
		 FROM TimeDimension
		WHERE ixYearWeek = @WeekNumber
		  AND ixDayInWeek = @iDay
	  END

	BEGIN TRY
	 BEGIN TRANSACTION

		 SELECT @AllocationID = AL_AllocationsID, 
				@AllocationsSPID = ASP_AllocationsSPID
		   FROM Allocations  
		  INNER JOIN ScheduledPersonTeam_LINK sptl ON AL_SchedulingTeamID = sptl.TeamID
		  INNER JOIN TimeDimension td ON td.ixYearWeek = AL_WeekNumber
		   LEFT JOIN AllocationsScheduledPersons ON AL_AllocationsID = ASP_AllocationsID 
												AND td.dDateTime  = ASP_DutyDate
												AND sptl.ScheduledPersonID = ASP_SchedulingPersonID 
		  WHERE sptl.ScheduledPersonID = @ScheduledPersonID 
			AND td.dDateTime =  @DutyDate
			AND sptl.IsHomeTeam = 1
			AND sptl.scheduledType = 1
			AND td.dDateTime BETWEEN sptl.StartDate AND sptl.EndDate
	  
		  IF ( ISNULL(@AllocationID,0) > 0 )
			BEGIN

			  IF(isNULL(@AllocationsSPID, 0) = 0)
			   BEGIN
				  EXEC @ReturnValue = usp_CreateScheduledPerson @AllocationID,
																NULL,
																@ScheduledPersonID,
																@DutyDate,
																@NetLogin,
																@AllocationsSPID = @AllocationsSPID OUTPUT

		       END
	

				UPDATE AP
					SET AP.ASP_RequestsStatus = CASE WHEN LR.LockIconColour IS NULL THEN NULL ELSE LR.LockIconColour END,
						ASP_RequestsCount = CASE WHEN LR.ReqCount IS NULL THEN NULL ELSE LR.ReqCount END,
						ASP_LockRequestsStatus = CASE WHEN LR.ShowLock IS NULL THEN NULL ELSE LR.ShowLock END,
						ASP_UpdatedBy = @UserID,
						ASP_UpdatedDate = GETUTCDATE()
				FROM AllocationsScheduledPersons AP
				LEFT JOIN (
				SELECT DISTINCT AL_AllocationsID,
					WeekNumber,
					iday,
					schedulingpersonid,
					max(ISNULL(ShowLock,0)) AS ShowLock,
					max(ISNULL(LockIconColour,0)) AS LockIconColour,
					ReqCount
				FROM 
				(
				SELECT td.ixYearWeek as WeekNumber, 
					AL.AL_AllocationsID,
					td.ixDayInWeek as iday, 
					sl.ScheduledPersonID as schedulingpersonid,
					CASE when LR.ID > 0 THEN 1
							WHEN RQ.ID > 0 THEN 1
					ELSE 0 END AS IsRequestAvailable,
					CASE WHEN LR.ID > 0 THEN 1 
							WHEN RT.AffectLocks = 1 AND RQ.Approved = 1 THEN 1
							ELSE 0 END AS ShowLock,
					CASE WHEN RQ.Approved = 1 AND RT.RequestsAllowed >= 1 THEN 3 --'O'
							WHEN ( CASE WHEN td.ixDayInWeek = 0 THEN RT.day_0
										WHEN td.ixDayInWeek = 1 THEN RT.day_1
										WHEN td.ixDayInWeek = 2 THEN RT.day_2
										WHEN td.ixDayInWeek = 3 THEN RT.day_3
										WHEN td.ixDayInWeek = 4 THEN RT.day_4
										WHEN td.ixDayInWeek = 5 THEN RT.day_5
										WHEN td.ixDayInWeek = 6 THEN RT.day_6
									END) = -1 THEN 1 --'B'
							WHEN RQ.isOK = 1 AND RT.RequestsAllowed >= 1 THEN 4 --'Y'
							WHEN RT.AllowOverLimit = 1 THEN 2 --'G' 
							ELSE 0 END AS LockIconColour,
					COUNT (ISNULL(rq.scheduledpersonid,0)) OVER ( Partition by rq.ddate, rq.scheduledpersonid) as ReqCount
				FROM Allocations AL
				INNER join Timedimension TD (nolock) on AL.AL_WeekNumber=TD.ixYearWeek		   
				INNER JOIN ScheduledPersonTeam_LINK SL (nolock) on sl.TeamID = AL.AL_SchedulingTeamID
				LEFT JOIN Requests RQ (nolock) ON RQ.dDate = TD.dDateTime AND RQ.ScheduledPersonID = sl.ScheduledPersonID 
																AND RQ.Deleted = 0
				LEFT JOIN RequestTypes (Nolock) RT ON RQ.RequestType = RT.ID
				LEFT JOIN LockRequests LR (nolock) ON LR.WeekNumber = td.ixYearWeek AND LR.iDay = td.ixDayInWeek
											AND LR.ScheduledPersonID = sl.ScheduledPersonID 
											AND LR.deleted = 0
				WHERE TD.dDateTime between sl.StartDate and sl.EndDate 
				AND TD.dDateTime = @DutyDate
				AND SL.ScheduledPersonID = @ScheduledPersonID
				AND sl.IsHomeTeam = 1
				AND sl.scheduledType = 1
				) FD where IsRequestAvailable = 1	
				GROUP BY WeekNumber,
							AL_AllocationsID,
							iday,
							schedulingpersonid,
							ReqCount	
				) LR on LR.schedulingpersonid = ap.ASP_SchedulingPersonID
					AND LR.AL_AllocationsID = ap.ASP_AllocationsID
					AND LR.iday = ap.ASP_iDay
			WHERE AP.ASP_AllocationsSPID = @AllocationsSPID

		 END

			IF ( @@TRANCOUNT  > 0 ) 
				BEGIN
				COMMIT  TRANSACTION 
				END   
	   
				RETURN 0
			
	 END TRY
				
	 BEGIN CATCH

	  IF ( @@TRANCOUNT  > 0 ) 
	   BEGIN
		ROLLBACK TRANSACTION
	   END

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
			@UserID	

	 	 RETURN 1

	END CATCH;
END
