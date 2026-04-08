USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_CreateAllocationsUpdate]    Script Date: 10/07/2025 12:59:21 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
ALTER           PROCEDURE  [dbo].[usp_CreateAllocationsUpdate]
@AllocationsID		 INT = NULL,
@AllocationsDutyID   INT = 0,
@AllocationsSPID    INT = 0,
@Status				INT,
@pNetLogin			VARCHAR(30)

AS
BEGIN

    SET NOCOUNT ON

    SET DATEFORMAT YMD
	
	DECLARE   @vuserID				INT;
         	
	BEGIN TRY
	 BEGIN TRANSACTION 

	   SELECT @vuserID = UD_UserID
		 from UserDetails
		WHERE UD_NetLogin = @pNetLogin	

		INSERT INTO AllocationsUpdated ( AU_AllocationsID,
										 AU_AllocationsDutyID,
										 AU_AllocationsSPID,
										 AU_Status,
										 AU_UpdatedBy,
										 AU_UpdatedDate)
							  VALUES  ( CASE WHEN ISNULL(@AllocationsID,0) = 0 THEN NULL ELSE @AllocationsID END,
										CASE WHEN ISNULL(@AllocationsDutyID,0) = 0 THEN NULL ELSE @AllocationsDutyID END,
										CASE WHEN ISNULL(@AllocationsSPID,0) = 0 THEN NULL ELSE @AllocationsSPID END,
										@Status,
										@vuserID,
										GETUTCDATE() )	   
	   
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
			(   ErrorNumber,
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
			@vuserID	

	 	 RETURN @@IDENTITY

	END CATCH;			
END	
