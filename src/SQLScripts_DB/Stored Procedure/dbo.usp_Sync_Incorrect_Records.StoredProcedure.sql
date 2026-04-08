USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_Sync_Incorrect_Records]    Script Date: 1/13/2026 9:24:12 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER         PROCEDURE [dbo].[usp_Sync_Incorrect_Records] 
	@SCP_ID VARCHAR(MAX),
	@UserId INT
AS
BEGIN

DECLARE @SCPID INT,
		@TeampayEmpNumber VARCHAR(10),
		@TeampayStaffNumber VARCHAR(11),
		@StartDate DATETIME,
		@EndDate DATETIME;

DECLARE @NetLogin NVARCHAR(50),
		@A7NetLogin NVARCHAR(50),
		@username VARCHAR(255), 
		@history VARCHAR(MAX), 
		@pageName VARCHAR(10),
		@TeampayStaffID	INT,
		@A7StaffID		INT,
		@A7UserId		INT,
		@DataList CURSOR;

BEGIN TRY

		SELECT @username = UD_DisplayName 
			FROM UserDetails 
			WHERE UD_UserID = @UserId 
		
		SET @DataList = CURSOR FOR                             
		SELECT SCP_ID, 
			   TeampayStaffNumber 
		  FROM StaffConfig_Processed 
		 WHERE SCP_ID IN(SELECT Value FROM STRING_SPLIT(@SCP_ID, ','))

		OPEN @DataList
		FETCH NEXT FROM @DataList 
		INTO @SCPID,  @TeampayStaffNumber
		
		WHILE @@FETCH_STATUS = 0
		  BEGIN
	 
                   SELECT @NetLogin = Netlogin,
						  @TeampayStaffID = StaffID
					 from StaffDetails
					where StaffNumber = @TeampayStaffNumber

                   SELECT @A7NetLogin = UD_NetLogin,
						  @A7StaffID = UD_TeampayStaffID,
						  @A7UserId = UD_UserID
					 from UserDetails
					 where UD_StaffNumber = @TeampayStaffNumber
					
					SET @pageName = 'Incorrect Records'
					
					IF EXISTS(SELECT 1 
								FROM StaffConfig_Processed AS A7SCP (nolock) 
								LEFT JOIN StaffDetails A7SD ON A7SD.StaffNumber=A7SCP.TeampayStaffNumber								
							WHERE A7SCP.EndDate >='2023-04-01'  
							  AND A7SCP.JobTitle = 'Integration: default posi' 
							  AND A7SCP.TeampaySCPID = @SCPID
							  )
					BEGIN
						SET @pageName = 'Leavers'
					END
					
					SET @history = ' -- Updated from TP Integration Page on '
									+FORMAT (getdate(), 'dd/MM/yyyy hh:mm')
									+' by A7.  Approved By '
									+ @username
									+ ' On ' + FORMAT (getdate(), 'dd/MM/yyyy') + ' At ' + FORMAT (getdate(), 'hh:mm')  
									+' in '+@pageName+' tab.'
                    
					IF ( @NetLogin <> @A7NetLogin)
                        BEGIN
                             Update Userdetails 
							 set UD_NetLogin = @NetLogin 
							 where UD_NetLogin = @A7NetLogin
                        END

					IF ( ISNULL(@A7StaffID,0) = 0  )
					 BEGIN
					   UPDATE UserDetails
						  SET UD_TeampayStaffID = @TeampayStaffID
						WHERE UD_UserID = @A7UserId
					 END

			 UPDATE UC
			    SET	UC.UC_EndDate=SCP.EndDate,
					UC.UC_JobTitle=SCP.JobTitle,
					UC.UC_EFT=SCP.EFT,
					UC.UC_CostCode=SCP.CostCode,
					UC.UC_AccGroupID=SCP.AccGroupID,
					UC.UC_AccGroup=SCP.AccGroup,
					UC.UC_PartTimeEDP=SCP.PartTimeEDP,
					UC.UC_PaymentTypeID=SCP.PaymentTypeID,
					UC.UC_ManualEDP=SCP.ManualEDP,
					UC.UC_UpdatedDate=SCP.LastModDate
					--,UC.UC_UpdatedBy=SCP.LastModBy
			FROM StaffConfig_Processed AS SCP (nolock)
		   INNER JOIN UserDetails UD on SCP.StaffID = UD.UD_TeampayStaffID
		   INNER JOIN UserConfigs AS UC (nolock) ON UD.UD_UserID = UC.UC_UserID AND UC.UC_SCP_ID = SCP.SCP_ID
		   WHERE SCP.SCP_ID = @SCPID		
		
			UPDATE StaffConfig_Processed
			   SET COMMENTS = 'Approved' 
			 WHERE SCP_ID = @SCPID

			FETCH NEXT FROM @DataList INTO @SCPID,  @TeampayStaffNumber

	END

	CLOSE @DataList

	DEALLOCATE @DataList

 END TRY
 BEGIN CATCH

	INSERT INTO ErrorLog
			VALUES
			(
			ERROR_NUMBER(),
			ERROR_STATE(),
			ERROR_SEVERITY(),
			ERROR_LINE(),
			'usp_Sync_Incorrect_Records',
			ERROR_MESSAGE(),
			GETDATE(),
			@UserId
			)
 END CATCH
END