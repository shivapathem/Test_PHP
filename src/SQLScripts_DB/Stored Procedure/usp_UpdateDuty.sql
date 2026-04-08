USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_UpdateDuty]    Script Date: 10/07/2025 13:26:54 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
ALTER           PROCEDURE  [dbo].[usp_UpdateDuty]
@AllocationsDutyID		INT,
@StartTime				INT,
@EndTime				INT,
@StartDate				DATETIME,
@EndDate				DATETIME,
@UserID					INT,
@IsShiftleader			INT = 0

AS
BEGIN

    SET NOCOUNT ON

    SET DATEFORMAT YMD
	
	BEGIN TRY

		UPDATE AllocationsDuties
		   SET AD_StartTimeSec			= @StartTime,
			   AD_EndTimeSec			= @EndTime,
			   AD_DutyStartTimeLocal	= @StartDate,
			   AD_DutyEndTimeLocal		= @EndDate,
			   AD_DutyStartTimeUTC		= @StartDate,
			   AD_DutyEndTimeUTC		= @EndDate,
			   AD_IsEditedDutyAttention = CASE WHEN @IsShiftleader = 1 THEN 1 ELSE 0 END,
			   AD_IsDutyEdited			= 1,
			   AD_UpdatedBy				= @UserID,
			   AD_UpdatedDate			= GETUTCDATE()
		WHERE AD_AllocationsDutyID = @AllocationsDutyID
		
	  RETURN 0
	  			
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
			@UserID	

	 	 RETURN @@IDENTITY  

	END CATCH;			
END	
